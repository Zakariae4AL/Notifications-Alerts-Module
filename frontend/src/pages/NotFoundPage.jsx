import { Link } from "react-router-dom";
import { useI18n } from "../i18n/I18nContext";

export default function NotFoundPage() {
  const { t } = useI18n();
  return (
    <div className="center">
      <h1>{t("notFound.title")}</h1>
      <Link to="/notifications">{t("notFound.back")}</Link>
    </div>
  );
}
