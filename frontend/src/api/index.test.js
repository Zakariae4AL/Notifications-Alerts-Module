import { beforeEach, describe, expect, it, vi } from "vitest";
import client from "./client";
import { alertsApi, authApi, notificationsApi, preferencesApi } from "./index";

// These tests pin the URLs and payloads to the Laravel routes in backend/routes/api.php.
describe("api endpoints", () => {
  beforeEach(() => {
    vi.restoreAllMocks();
    for (const method of ["get", "post", "patch", "put", "delete"]) {
      vi.spyOn(client, method).mockResolvedValue({ data: { data: "payload", unread_count: 7, updated: 3 } });
    }
  });

  it("auth", async () => {
    await authApi.login("a@b.c", "secret");
    expect(client.post).toHaveBeenCalledWith("/auth/login", { email: "a@b.c", password: "secret", device_name: "web" });

    await authApi.logout();
    expect(client.post).toHaveBeenCalledWith("/auth/logout");

    expect(await authApi.me()).toBe("payload");
    expect(client.get).toHaveBeenCalledWith("/me");

    expect(await authApi.update({ locale: "fr" })).toBe("payload");
    expect(client.patch).toHaveBeenCalledWith("/me", { locale: "fr" });
  });

  it("notifications", async () => {
    await notificationsApi.list({ status: "unread", type: "trip_delayed", page: 2 });
    expect(client.get).toHaveBeenCalledWith("/notifications", {
      params: { status: "unread", page: 2, per_page: 10, type: "trip_delayed" },
    });

    await notificationsApi.list();
    expect(client.get).toHaveBeenLastCalledWith("/notifications", { params: { status: "all", page: 1, per_page: 10 } });

    expect(await notificationsApi.unreadCount()).toBe(7);
    expect(client.get).toHaveBeenLastCalledWith("/notifications/unread-count");

    await notificationsApi.markAsRead("id-1");
    expect(client.patch).toHaveBeenCalledWith("/notifications/id-1/read");

    await notificationsApi.markAsUnread("id-1");
    expect(client.patch).toHaveBeenCalledWith("/notifications/id-1/unread");

    expect(await notificationsApi.markAllAsRead()).toBe(3);
    expect(client.patch).toHaveBeenCalledWith("/notifications/read-all");

    await notificationsApi.remove("id-1");
    expect(client.delete).toHaveBeenCalledWith("/notifications/id-1");
  });

  it("preferences and alert types", async () => {
    expect(await preferencesApi.get()).toBe("payload");
    expect(client.get).toHaveBeenCalledWith("/notification-preferences");

    await preferencesApi.save([{ type: "trip_started", in_app: false }]);
    expect(client.put).toHaveBeenCalledWith("/notification-preferences", {
      preferences: [{ type: "trip_started", in_app: false }],
    });

    expect(await alertsApi.types()).toBe("payload");
    expect(client.get).toHaveBeenLastCalledWith("/alerts/types");
  });
});
