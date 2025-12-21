import { useEffect, useState } from "react";
import api from "../services/api";

export default function Notifications() {
  const [notifications, setNotifications] = useState([]);

  useEffect(() => {
    api.get("/notifications")   // ← must match the Laravel route
      .then(res => setNotifications(res.data))
      .catch(err => console.error(err));
  }, []);

  return (
    <div>
      <h2>Notifications</h2>
      {notifications.map(n => (
        <div key={n.id}>{n.message}</div>
      ))}
    </div>
  );
}
