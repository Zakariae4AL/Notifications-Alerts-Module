export const parent = { id: 2, name: "Sara Parent", email: "parent@example.com", locale: "en", is_admin: false };

export const makeNotification = (overrides = {}) => ({
  id: crypto.randomUUID(),
  type: "trip_started",
  label: "Trip started",
  severity: "info",
  message: "Trip B-12 has started.",
  payload: { trip: "B-12" },
  is_read: false,
  read_at: null,
  created_at: "2026-11-12T08:30:00+00:00",
  ...overrides,
});

export const page = (data, meta = {}) => ({
  data,
  meta: { current_page: 1, last_page: 1, total: data.length, unread_count: data.filter((n) => !n.is_read).length, ...meta },
});

export const preferences = [
  { type: "trip_started", label: "Trip started", severity: "info", in_app: true, email: false },
  { type: "passenger_absent", label: "Passenger absent", severity: "critical", in_app: true, email: true },
];

export const alertTypes = [
  { type: "trip_started", label: "Trip started", severity: "info", required_data: ["trip"] },
  { type: "trip_delayed", label: "Trip delayed", severity: "warning", required_data: ["trip", "delay"] },
];
