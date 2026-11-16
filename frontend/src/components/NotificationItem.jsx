import { useI18n } from "../i18n/I18nContext";

export default function NotificationItem({ notification, onToggleRead, onDelete }) {
  const { t, formatDate } = useI18n();
  const { id, label, message, severity, is_read: isRead, created_at: createdAt } = notification;

  return (
    <li className={`notification severity-${severity} ${isRead ? "is-read" : "is-unread"}`}>
      <div className="notification-body">
        <div className="notification-meta">
          <span className={`pill pill-${severity}`}>{t(`severity.${severity}`)}</span>
          <span className="notification-label">{label}</span>
          {!isRead && <span className="dot" aria-hidden="true" />}
        </div>
        <p className="notification-message">{message}</p>
        <time className="muted small" dateTime={createdAt}>
          {formatDate(createdAt)}
        </time>
      </div>
      <div className="notification-actions">
        <button type="button" className="btn btn-small" onClick={() => onToggleRead(id, isRead)}>
          {isRead ? t("inbox.markUnread") : t("inbox.markRead")}
        </button>
        <button
          type="button"
          className="btn btn-small btn-danger-ghost"
          onClick={() => onDelete(id)}
          aria-label={`${t("inbox.delete")}: ${message}`}
        >
          {t("inbox.delete")}
        </button>
      </div>
    </li>
  );
}
