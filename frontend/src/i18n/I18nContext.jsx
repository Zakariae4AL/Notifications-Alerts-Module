import { createContext, useCallback, useContext, useEffect, useMemo, useState } from "react";
import { LOCALE_KEY } from "../api/client";
import messages, { SUPPORTED_LOCALES } from "./messages";

const I18nContext = createContext(null);

function initialLocale() {
  const saved = localStorage.getItem(LOCALE_KEY);
  if (SUPPORTED_LOCALES.includes(saved)) return saved;
  const browser = navigator.language?.slice(0, 2);
  return SUPPORTED_LOCALES.includes(browser) ? browser : "en";
}

export function I18nProvider({ children }) {
  const [locale, setLocaleState] = useState(initialLocale);

  useEffect(() => {
    localStorage.setItem(LOCALE_KEY, locale);
    document.documentElement.lang = locale;
  }, [locale]);

  const setLocale = useCallback((next) => {
    if (SUPPORTED_LOCALES.includes(next)) {
      // Write immediately so the very next API call carries the new language.
      localStorage.setItem(LOCALE_KEY, next);
      setLocaleState(next);
    }
  }, []);

  const t = useCallback(
    (key, params = {}) => {
      const template = messages[locale][key] ?? messages.en[key] ?? key;
      return template.replace(/\{(\w+)\}/g, (_, name) => params[name] ?? `{${name}}`);
    },
    [locale],
  );

  const formatDate = useCallback(
    (iso) =>
      new Intl.DateTimeFormat(locale === "fr" ? "fr-FR" : "en-GB", {
        dateStyle: "medium",
        timeStyle: "short",
      }).format(new Date(iso)),
    [locale],
  );

  const value = useMemo(() => ({ locale, setLocale, t, formatDate }), [locale, setLocale, t, formatDate]);

  return <I18nContext.Provider value={value}>{children}</I18nContext.Provider>;
}

export function useI18n() {
  const context = useContext(I18nContext);
  if (!context) throw new Error("useI18n must be used inside <I18nProvider>");
  return context;
}
