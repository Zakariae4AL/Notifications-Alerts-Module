import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { alertsApi, authApi, notificationsApi } from "../api";
import { LOCALE_KEY, TOKEN_KEY } from "../api/client";
import { page, parent } from "../test/fixtures";
import { renderApp } from "../test/utils";

vi.mock("../api");

describe("Layout", () => {
  beforeEach(() => {
    vi.resetAllMocks();
    localStorage.setItem(LOCALE_KEY, "en");
    localStorage.setItem(TOKEN_KEY, "tok");
    authApi.me.mockResolvedValue(parent);
    notificationsApi.list.mockResolvedValue(page([]));
    notificationsApi.unreadCount.mockResolvedValue(4);
    alertsApi.types.mockResolvedValue([]);
  });

  it("shows the unread badge in the navigation", async () => {
    notificationsApi.list.mockResolvedValue(page([], { unread_count: 4 }));
    renderApp("/notifications");

    expect(await screen.findByLabelText("4 unread")).toHaveTextContent("4");
  });

  it("saves the language on the account when switching", async () => {
    authApi.update.mockResolvedValue({ ...parent, locale: "fr" });
    renderApp("/notifications");
    await screen.findByRole("heading", { name: "Inbox" });

    await userEvent.click(screen.getByRole("button", { name: "FR" }));

    expect(authApi.update).toHaveBeenCalledWith({ locale: "fr" });
    expect(await screen.findByRole("heading", { name: "Boîte de réception" })).toBeInTheDocument();
    // Messages are translated server-side, so the inbox is fetched again.
    await waitFor(() => expect(notificationsApi.list).toHaveBeenCalledTimes(2));
  });

  it("logs out", async () => {
    authApi.logout.mockResolvedValue();
    renderApp("/notifications");

    await userEvent.click(await screen.findByRole("button", { name: "Log out" }));

    expect(await screen.findByRole("heading", { name: "Sign in" })).toBeInTheDocument();
    expect(localStorage.getItem(TOKEN_KEY)).toBeNull();
  });

  it("shows a not-found page for unknown routes", async () => {
    renderApp("/does-not-exist");

    expect(await screen.findByRole("heading", { name: "Page not found" })).toBeInTheDocument();
  });
});
