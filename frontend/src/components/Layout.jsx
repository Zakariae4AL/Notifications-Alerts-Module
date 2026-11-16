import { NavLink, Outlet } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { UnreadProvider, useUnread } from "../context/UnreadContext";
import { useI18n } from "../i18n/I18nContext";
import LanguageSwitch from "./LanguageSwitch";

function UnreadBadge() {
  const { count } = useUnread();
  if (!count) return null;
  return (
    <span className="badge" aria-label={`${count} unread`}>
      {count > 99 ? "99+" : count}
    </span>
  );
}

export default function Layout() {
  const { user, logout, changeLocale } = useAuth();
  const { t } = useI18n();

  return (
    <UnreadProvider>
      <div className="shell">
        <header className="topbar">
          <div className="brand">
            <span className="brand-mark" aria-hidden="true">
              <BellIcon />
            </span>
            <span className="brand-name">{t("app.name")}</span>
          </div>
          <nav className="nav" aria-label="Main">
            <NavLink to="/notifications">
              {t("nav.inbox")} <UnreadBadge />
            </NavLink>
            <NavLink to="/preferences">{t("nav.preferences")}</NavLink>
          </nav>
          <div className="topbar-actions">
            <LanguageSwitch onChange={changeLocale} />
            <span className="user-name" title={user?.email}>
              {user?.name}
            </span>
            <button type="button" className="btn btn-ghost" onClick={logout}>
              {t("nav.logout")}
            </button>
          </div>
        </header>
        <main className="content">
          <Outlet />
        </main>
      </div>
    </UnreadProvider>
  );
}

export function BellIcon() {
  return (
    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
      <path d="M12 2.5a6 6 0 0 0-6 6v4.3l-1.7 2.6A1 1 0 0 0 5.1 17h13.8a1 1 0 0 0 .8-1.6L18 12.8V8.5a6 6 0 0 0-6-6Zm-2.6 16a2.6 2.6 0 0 0 5.2 0Z" />
    </svg>
  );
}
