import { useEffect, useState } from "react";
import axios from "axios";

export default function Notifications() {
  const [notifications, setNotifications] = useState([]);

  useEffect(() => {
    axios.get("/api/notifications").then(res => {
      setNotifications(res.data);
    });
  }, []);

  const markRead = (id) => {
    axios.put(`/api/notifications/${id}/read`).then(() => {
      setNotifications(n =>
        n.map(notif => notif.id === id ? { ...notif, is_read: true } : notif)
      );
    });
  };

  return (
    <div>
      <h2>Notifications</h2>
      {notifications.map(n => (
        <div key={n.id} style={{ fontWeight: n.is_read ? "normal" : "bold" }}>
          {n.message}
          {!n.is_read && <button onClick={() => markRead(n.id)}>Read</button>}
        </div>
      ))}
    </div>
  );
}
