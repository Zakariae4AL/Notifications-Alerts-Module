import { useI18n } from "../i18n/I18nContext";
import { SUPPORTED_LOCALES } from "../i18n/messages";

export default function LanguageSwitch({ onChange }) {
  const { locale, setLocale, t } = useI18n();
  const change = onChange ?? setLocale;

  return (
    <div className="lang-switch" role="group" aria-label={t("nav.language")}>
      {SUPPORTED_LOCALES.map((code) => (
        <button
          key={code}
          type="button"
          aria-pressed={locale === code}
          className={locale === code ? "active" : ""}
          onClick={() => change(code)}
        >
          {code.toUpperCase()}
        </button>
      ))}
    </div>
  );
}
