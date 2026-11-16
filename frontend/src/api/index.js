import client from "./client";

export const authApi = {
  login: (email, password) =>
    client.post("/auth/login", { email, password, device_name: "web" }).then((r) => r.data),
  logout: () => client.post("/auth/logout"),
  me: () => client.get("/me").then((r) => r.data.data),
  update: (changes) => client.patch("/me", changes).then((r) => r.data.data),
};

export const notificationsApi = {
  list: ({ status = "all", type, page = 1, perPage = 10 } = {}) =>
    client
      .get("/notifications", {
        params: { status, page, per_page: perPage, ...(type ? { type } : {}) },
      })
      .then((r) => r.data),
  unreadCount: () => client.get("/notifications/unread-count").then((r) => r.data.unread_count),
  markAsRead: (id) => client.patch(`/notifications/${id}/read`).then((r) => r.data.data),
  markAsUnread: (id) => client.patch(`/notifications/${id}/unread`).then((r) => r.data.data),
  markAllAsRead: () => client.patch("/notifications/read-all").then((r) => r.data.updated),
  remove: (id) => client.delete(`/notifications/${id}`),
};

export const preferencesApi = {
  get: () => client.get("/notification-preferences").then((r) => r.data.data),
  save: (preferences) =>
    client.put("/notification-preferences", { preferences }).then((r) => r.data),
};

export const alertsApi = {
  types: () => client.get("/alerts/types").then((r) => r.data.data),
};
