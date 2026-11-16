import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { authApi, notificationsApi, alertsApi } from "../api";
import { LOCALE_KEY, TOKEN_KEY } from "../api/client";
import { page, parent } from "../test/fixtures";
import { renderApp } from "../test/utils";

vi.mock("../api");

describe("LoginPage", () => {
  beforeEach(() => {
    vi.resetAllMocks();
    localStorage.setItem(LOCALE_KEY, "en");
    notificationsApi.list.mockResolvedValue(page([]));
    notificationsApi.unreadCount.mockResolvedValue(0);
    alertsApi.types.mockResolvedValue([]);
  });

  it("redirects anonymous visitors to the login page", () => {
    renderApp("/notifications");
    expect(screen.getByRole("heading", { name: "Sign in" })).toBeInTheDocument();
  });

  it("logs in, stores the token and opens the inbox", async () => {
    authApi.login.mockResolvedValue({ token: "tok", user: parent });
    renderApp("/login");

    await userEvent.type(screen.getByLabelText("Email"), "parent@example.com");
    await userEvent.type(screen.getByLabelText("Password"), "password");
    await userEvent.click(screen.getByRole("button", { name: "Sign in" }));

    expect(await screen.findByRole("heading", { name: "Inbox" })).toBeInTheDocument();
    expect(authApi.login).toHaveBeenCalledWith("parent@example.com", "password");
    expect(localStorage.getItem(TOKEN_KEY)).toBe("tok");
    expect(screen.getByText("Sara Parent")).toBeInTheDocument();
  });

  it("shows the API validation error", async () => {
    authApi.login.mockRejectedValue({
      response: { status: 422, data: { errors: { email: ["These credentials do not match our records."] } } },
    });
    renderApp("/login");

    await userEvent.type(screen.getByLabelText("Email"), "x@example.com");
    await userEvent.type(screen.getByLabelText("Password"), "nope");
    await userEvent.click(screen.getByRole("button", { name: "Sign in" }));

    expect(await screen.findByRole("alert")).toHaveTextContent("These credentials do not match our records.");
    expect(localStorage.getItem(TOKEN_KEY)).toBeNull();
  });

  it("fills the form from a demo account", async () => {
    renderApp("/login");

    await userEvent.click(screen.getByRole("button", { name: "admin@example.com" }));

    expect(screen.getByLabelText("Email")).toHaveValue("admin@example.com");
    expect(screen.getByLabelText("Password")).toHaveValue("password");
  });

  it("can be displayed in French", async () => {
    renderApp("/login");

    await userEvent.click(screen.getByRole("button", { name: "FR" }));

    expect(screen.getByRole("heading", { name: "Connexion" })).toBeInTheDocument();
    expect(screen.getByLabelText("Mot de passe")).toBeInTheDocument();
  });

  it("restores the session from a saved token", async () => {
    localStorage.setItem(TOKEN_KEY, "saved");
    authApi.me.mockResolvedValue({ ...parent, locale: "fr" });

    renderApp("/notifications");

    expect(await screen.findByRole("heading", { name: "Boîte de réception" })).toBeInTheDocument();
  });
});
