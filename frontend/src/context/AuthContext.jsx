import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { authApi } from "../api";
import { TOKEN_KEY } from "../api/client";
import { useI18n } from "../i18n/I18nContext";

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const { setLocale } = useI18n();
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(() => Boolean(localStorage.getItem(TOKEN_KEY)));

  // Restore the session from a saved token.
  useEffect(() => {
    if (!localStorage.getItem(TOKEN_KEY)) return;
    authApi
      .me()
      .then((me) => {
        setUser(me);
        setLocale(me.locale);
      })
      .catch(() => localStorage.removeItem(TOKEN_KEY))
      .finally(() => setLoading(false));
  }, [setLocale]);

  useEffect(() => {
    const onExpired = () => setUser(null);
    window.addEventListener("auth:expired", onExpired);
    return () => window.removeEventListener("auth:expired", onExpired);
  }, []);

  const login = useCallback(
    async (email, password) => {
      const { token, user: me } = await authApi.login(email, password);
      localStorage.setItem(TOKEN_KEY, token);
      setUser(me);
      setLocale(me.locale);
      return me;
    },
    [setLocale],
  );

  const logout = useCallback(async () => {
    try {
      await authApi.logout();
    } finally {
      localStorage.removeItem(TOKEN_KEY);
      setUser(null);
    }
  }, []);

  /** Persist the language on the account so e-mails use it too. */
  const changeLocale = useCallback(
    async (locale) => {
      setLocale(locale);
      if (user) setUser(await authApi.update({ locale }));
    },
    [setLocale, user],
  );

  const value = useMemo(
    () => ({ user, loading, login, logout, changeLocale }),
    [user, loading, login, logout, changeLocale],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used inside <AuthProvider>");
  return context;
}
