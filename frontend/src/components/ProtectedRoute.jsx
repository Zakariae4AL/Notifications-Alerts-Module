import { Navigate, useLocation } from "react-router-dom";
import { useAuth } from "../context/AuthContext";
import { useI18n } from "../i18n/I18nContext";

export default function ProtectedRoute({ children }) {
  const { user, loading } = useAuth();
  const { t } = useI18n();
  const location = useLocation();

  if (loading) return <p className="center muted">{t("common.loading")}</p>;
  if (!user) return <Navigate to="/login" replace state={{ from: location }} />;
  return children;
}
