import { useState } from "react";
import { Navigate, useLocation, useNavigate } from "react-router-dom";
import { errorMessage } from "../api/client";
import { BellIcon } from "../components/Layout";
import LanguageSwitch from "../components/LanguageSwitch";
import { useAuth } from "../context/AuthContext";
import { useI18n } from "../i18n/I18nContext";

const DEMO_ACCOUNTS = [
  { email: "parent@example.com", role: "Parent" },
  { email: "admin@example.com", role: "Admin" },
];

export default function LoginPage() {
  const { user, login } = useAuth();
  const { t } = useI18n();
  const navigate = useNavigate();
  const location = useLocation();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState(null);
  const [submitting, setSubmitting] = useState(false);

  if (user) return <Navigate to="/notifications" replace />;

  const submit = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    setError(null);
    try {
      await login(email, password);
      navigate(location.state?.from?.pathname ?? "/notifications", { replace: true });
    } catch (err) {
      setError(errorMessage(err, t("login.error")));
      setSubmitting(false);
    }
  };

  return (
    <div className="auth-page">
      <div className="auth-card">
        <div className="auth-header">
          <span className="brand-mark large" aria-hidden="true">
            <BellIcon />
          </span>
          <LanguageSwitch />
        </div>
        <h1>{t("login.title")}</h1>
        <p className="muted">{t("app.tagline")}</p>

        <form onSubmit={submit} noValidate>
          <label>
            {t("login.email")}
            <input
              type="email"
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
            />
          </label>
          <label>
            {t("login.password")}
            <input
              type="password"
              autoComplete="current-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </label>
          {error && (
            <p role="alert" className="alert alert-error">
              {error}
            </p>
          )}
          <button type="submit" className="btn btn-primary btn-block" disabled={submitting}>
            {submitting ? t("login.submitting") : t("login.submit")}
          </button>
        </form>

        <div className="demo-accounts">
          <p className="small muted">{t("login.demo")}</p>
          <ul>
            {DEMO_ACCOUNTS.map((account) => (
              <li key={account.email}>
                <button
                  type="button"
                  className="link"
                  onClick={() => {
                    setEmail(account.email);
                    setPassword("password");
                  }}
                >
                  {account.email}
                </button>
                <span className="small muted"> · {account.role} · password</span>
              </li>
            ))}
          </ul>
        </div>
      </div>
    </div>
  );
}
