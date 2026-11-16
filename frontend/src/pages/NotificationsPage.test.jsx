import { screen, waitFor, within } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { alertsApi, notificationsApi } from "../api";
import { LOCALE_KEY } from "../api/client";
import { alertTypes, makeNotification, page } from "../test/fixtures";
import { renderPage } from "../test/utils";
import NotificationsPage from "./NotificationsPage";

vi.mock("../api");

const started = makeNotification({ id: "n1", message: "Trip B-12 has started." });
const absent = makeNotification({
  id: "n2",
  type: "passenger_absent",
  label: "Passenger absent",
  severity: "critical",
  message: "Lina was marked absent on trip B-07.",
});
const done = makeNotification({ id: "n3", message: "Trip B-12 has been completed.", is_read: true });

describe("NotificationsPage", () => {
  beforeEach(() => {
    vi.resetAllMocks();
    localStorage.setItem(LOCALE_KEY, "en");
    notificationsApi.list.mockResolvedValue(page([started, absent, done]));
    notificationsApi.unreadCount.mockResolvedValue(2);
    alertsApi.types.mockResolvedValue(alertTypes);
  });

  it("lists notifications with their severity and unread count", async () => {
    renderPage(<NotificationsPage />);

    expect(await screen.findByText("Lina was marked absent on trip B-07.")).toBeInTheDocument();
    expect(screen.getAllByRole("listitem")).toHaveLength(3);
    expect(screen.getByText("Critical")).toBeInTheDocument();
    expect(screen.getByText("2 unread")).toBeInTheDocument();
  });

  it("filters by status", async () => {
    renderPage(<NotificationsPage />);
    await screen.findByText("Trip B-12 has started.");

    await userEvent.click(screen.getByRole("button", { name: "Unread" }));

    await waitFor(() =>
      expect(notificationsApi.list).toHaveBeenLastCalledWith({ status: "unread", type: "", page: 1 }),
    );
  });

  it("filters by type using the types returned by the API", async () => {
    renderPage(<NotificationsPage />);
    await screen.findByRole("option", { name: "Trip delayed" });

    await userEvent.selectOptions(screen.getByLabelText("Type"), "trip_delayed");

    await waitFor(() =>
      expect(notificationsApi.list).toHaveBeenLastCalledWith({ status: "all", type: "trip_delayed", page: 1 }),
    );
  });

  it("marks a notification as read in place", async () => {
    notificationsApi.markAsRead.mockResolvedValue({ ...started, is_read: true });
    renderPage(<NotificationsPage />);

    const item = (await screen.findByText("Trip B-12 has started.")).closest("li");
    await userEvent.click(within(item).getByRole("button", { name: "Mark as read" }));

    expect(notificationsApi.markAsRead).toHaveBeenCalledWith("n1");
    expect(await within(item).findByRole("button", { name: "Mark as unread" })).toBeInTheDocument();
    expect(screen.getByText("1 unread")).toBeInTheDocument();
  });

  it("marks a read notification as unread", async () => {
    notificationsApi.markAsUnread.mockResolvedValue({ ...done, is_read: false });
    renderPage(<NotificationsPage />);

    const item = (await screen.findByText("Trip B-12 has been completed.")).closest("li");
    await userEvent.click(within(item).getByRole("button", { name: "Mark as unread" }));

    expect(notificationsApi.markAsUnread).toHaveBeenCalledWith("n3");
    expect(await screen.findByText("3 unread")).toBeInTheDocument();
  });

  it("marks everything as read and reloads", async () => {
    notificationsApi.markAllAsRead.mockResolvedValue(2);
    renderPage(<NotificationsPage />);
    await screen.findByText("Trip B-12 has started.");

    notificationsApi.list.mockResolvedValue(page([{ ...started, is_read: true }]));
    await userEvent.click(screen.getByRole("button", { name: "Mark all as read" }));

    expect(notificationsApi.markAllAsRead).toHaveBeenCalledOnce();
    expect(await screen.findByText("0 unread")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Mark all as read" })).toBeDisabled();
  });

  it("deletes a notification", async () => {
    notificationsApi.remove.mockResolvedValue();
    renderPage(<NotificationsPage />);
    await screen.findByText("Trip B-12 has started.");

    notificationsApi.list.mockResolvedValue(page([absent, done]));
    await userEvent.click(screen.getByRole("button", { name: "Delete: Trip B-12 has started." }));

    expect(notificationsApi.remove).toHaveBeenCalledWith("n1");
    await waitFor(() => expect(screen.queryByText("Trip B-12 has started.")).not.toBeInTheDocument());
  });

  it("shows an empty state", async () => {
    notificationsApi.list.mockResolvedValue(page([]));
    renderPage(<NotificationsPage />);

    expect(await screen.findByText("You're all caught up.")).toBeInTheDocument();
  });

  it("paginates", async () => {
    notificationsApi.list.mockResolvedValue(page([started], { current_page: 1, last_page: 3 }));
    renderPage(<NotificationsPage />);

    expect(await screen.findByText("Page 1 of 3")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Previous" })).toBeDisabled();

    await userEvent.click(screen.getByRole("button", { name: "Next" }));

    await waitFor(() => expect(notificationsApi.list).toHaveBeenLastCalledWith({ status: "all", type: "", page: 2 }));
  });

  it("shows an error and lets the user retry", async () => {
    notificationsApi.list.mockRejectedValueOnce(new Error("network"));
    renderPage(<NotificationsPage />);

    expect(await screen.findByRole("alert")).toHaveTextContent("Could not load notifications.");

    await userEvent.click(screen.getByRole("button", { name: "Retry" }));

    expect(await screen.findByText("Trip B-12 has started.")).toBeInTheDocument();
  });
});
