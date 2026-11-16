import { render } from "@testing-library/react";
import { MemoryRouter } from "react-router-dom";
import { AppRoutes } from "../App";
import { AuthProvider } from "../context/AuthContext";
import { UnreadProvider } from "../context/UnreadContext";
import { I18nProvider } from "../i18n/I18nContext";

/** Render the whole route tree at a given URL, with every provider. */
export function renderApp(route = "/") {
  return render(
    <I18nProvider>
      <AuthProvider>
        <MemoryRouter initialEntries={[route]}>
          <AppRoutes />
        </MemoryRouter>
      </AuthProvider>
    </I18nProvider>,
  );
}

/** Render a single page inside the i18n + unread providers. */
export function renderPage(ui) {
  return render(
    <I18nProvider>
      <UnreadProvider>
        <MemoryRouter>{ui}</MemoryRouter>
      </UnreadProvider>
    </I18nProvider>,
  );
}
