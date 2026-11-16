import { useEffect, useState } from "react";
import { preferencesApi } from "../api";
import { errorMessage } from "../api/client";
import { useI18n } from "../i18n/I18nContext";

const CHANNELS = ["in_app", "email"];

export default function PreferencesPage() {
  const { t, locale } = useI18n();
  const [rows, setRows] = useState(null);
  const [loadError, setLoadError] = useState(false);
  const [saving, setSaving] = useState(false);
  const [feedback, setFeedback] = useState(null); // { kind: 'success' | 'error', text }

  useEffect(() => {
    preferencesApi
      .get()
      .then(setRows)
      .catch(() => setLoadError(true));
  }, [locale]);

  const toggle = (type, channel) => {
    setFeedback(null);
    setRows((prev) => prev.map((row) => (row.type === type ? { ...row, [channel]: !row[channel] } : row)));
  };

  const save = async (event) => {
    event.preventDefault();
    setSaving(true);
    try {
      const payload = rows.map(({ type, in_app: inApp, email }) => ({ type, in_app: inApp, email }));
      const res = await preferencesApi.save(payload);
      setRows(res.data);
      setFeedback({ kind: "success", text: res.message });
    } catch (err) {
      setFeedback({ kind: "error", text: errorMessage(err, t("prefs.error")) });
    } finally {
      setSaving(false);
    }
  };

  if (loadError) return <p role="alert" className="alert alert-error">{t("prefs.loadError")}</p>;
  if (!rows) return <p className="muted">{t("common.loading")}</p>;

  return (
    <section>
      <div className="page-header">
        <div>
          <h1>{t("prefs.title")}</h1>
          <p className="muted">{t("prefs.subtitle")}</p>
        </div>
      </div>

      <form onSubmit={save} className="card">
        <table className="prefs-table">
          <thead>
            <tr>
              <th scope="col">{t("prefs.event")}</th>
              <th scope="col">{t("prefs.inApp")}</th>
              <th scope="col">{t("prefs.email")}</th>
            </tr>
          </thead>
          <tbody>
            {rows.map((row) => (
              <tr key={row.type}>
                <th scope="row">
                  <span className="event-name">
                    <span className={`pill pill-${row.severity}`}>{t(`severity.${row.severity}`)}</span>
                    {row.label}
                  </span>
                </th>
                {CHANNELS.map((channel) => (
                  <td key={channel}>
                    <label className="switch">
                      <input
                        type="checkbox"
                        checked={row[channel]}
                        onChange={() => toggle(row.type, channel)}
                        aria-label={`${row.label} — ${channel === "in_app" ? t("prefs.inApp") : t("prefs.email")}`}
                      />
                      <span className="slider" aria-hidden="true" />
                    </label>
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>

        <div className="form-footer">
          {feedback && (
            <p role={feedback.kind === "error" ? "alert" : "status"} className={`alert alert-${feedback.kind}`}>
              {feedback.text}
            </p>
          )}
          <button type="submit" className="btn btn-primary" disabled={saving}>
            {saving ? t("prefs.saving") : t("prefs.save")}
          </button>
        </div>
      </form>
    </section>
  );
}
