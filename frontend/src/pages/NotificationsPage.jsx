import { useEffect, useState } from "react";
import { alertsApi, notificationsApi } from "../api";
import NotificationItem from "../components/NotificationItem";
import { useUnread } from "../context/UnreadContext";
import { useI18n } from "../i18n/I18nContext";

const STATUSES = ["all", "unread", "read"];

export default function NotificationsPage() {
  const { t, locale } = useI18n();
  const { count, setCount } = useUnread();
  const [status, setStatus] = useState("all");
  const [type, setType] = useState("");
  const [page, setPage] = useState(1);
  const [types, setTypes] = useState([]);
  const [result, setResult] = useState({ data: [], meta: null });
  const [state, setState] = useState("loading"); // loading | ready | error

  const [reloadKey, setReloadKey] = useState(0);
  const reload = () => setReloadKey((k) => k + 1);

  // Reload when filters, page or language change (messages are translated server-side).
  useEffect(() => {
    let cancelled = false;
    notificationsApi
      .list({ status, type, page })
      .then((res) => {
        if (cancelled) return;
        setResult(res);
        setCount(res.meta.unread_count);
        setState("ready");
      })
      .catch(() => !cancelled && setState("error"));
    return () => {
      cancelled = true;
    };
  }, [status, type, page, locale, reloadKey, setCount]);

  useEffect(() => {
    alertsApi.types().then(setTypes).catch(() => setTypes([]));
  }, [locale]);

  const changeFilter = (setter) => (value) => {
    setter(value);
    setPage(1);
  };

  const retry = () => {
    setState("loading");
    reload();
  };

  const toggleRead = async (id, isRead) => {
    const updated = isRead ? await notificationsApi.markAsUnread(id) : await notificationsApi.markAsRead(id);
    setResult((prev) => ({ ...prev, data: prev.data.map((n) => (n.id === id ? updated : n)) }));
    setCount((c) => c + (isRead ? 1 : -1));
  };

  const remove = async (id) => {
    await notificationsApi.remove(id);
    reload();
  };

  const markAll = async () => {
    await notificationsApi.markAllAsRead();
    reload();
  };

  const { data, meta } = result;
  const filtered = status !== "all" || type !== "";

  return (
    <section>
      <div className="page-header">
        <div>
          <h1>{t("inbox.title")}</h1>
          <p className="muted">{t("inbox.unread", { count })}</p>
        </div>
        <button type="button" className="btn btn-primary" onClick={markAll} disabled={count === 0}>
          {t("inbox.markAll")}
        </button>
      </div>

      <div className="toolbar">
        <div className="segmented" role="group" aria-label="Status">
          {STATUSES.map((s) => (
            <button
              key={s}
              type="button"
              aria-pressed={status === s}
              className={status === s ? "active" : ""}
              onClick={() => changeFilter(setStatus)(s)}
            >
              {t(`inbox.filter.${s}`)}
            </button>
          ))}
        </div>
        <label className="inline-label">
          <span>{t("inbox.filter.type")}</span>
          <select value={type} onChange={(e) => changeFilter(setType)(e.target.value)}>
            <option value="">{t("inbox.filter.anyType")}</option>
            {types.map((option) => (
              <option key={option.type} value={option.type}>
                {option.label}
              </option>
            ))}
          </select>
        </label>
      </div>

      {state === "loading" && <p className="muted">{t("common.loading")}</p>}

      {state === "error" && (
        <div role="alert" className="alert alert-error">
          {t("inbox.error")}{" "}
          <button type="button" className="link" onClick={retry}>
            {t("inbox.retry")}
          </button>
        </div>
      )}

      {state === "ready" && data.length === 0 && (
        <div className="empty">{filtered ? t("inbox.emptyFiltered") : t("inbox.empty")}</div>
      )}

      {state === "ready" && data.length > 0 && (
        <>
          <ul className="notification-list">
            {data.map((notification) => (
              <NotificationItem
                key={notification.id}
                notification={notification}
                onToggleRead={toggleRead}
                onDelete={remove}
              />
            ))}
          </ul>
          {meta.last_page > 1 && (
            <nav className="pagination" aria-label="Pagination">
              <button type="button" className="btn btn-small" disabled={page <= 1} onClick={() => setPage(page - 1)}>
                {t("inbox.previous")}
              </button>
              <span className="muted small">{t("inbox.page", { page: meta.current_page, last: meta.last_page })}</span>
              <button
                type="button"
                className="btn btn-small"
                disabled={page >= meta.last_page}
                onClick={() => setPage(page + 1)}
              >
                {t("inbox.next")}
              </button>
            </nav>
          )}
        </>
      )}
    </section>
  );
}
