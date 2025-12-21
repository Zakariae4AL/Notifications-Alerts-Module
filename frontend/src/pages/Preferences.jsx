import axios from "axios";
import { useState } from "react";

export default function Preferences() {
  const [prefs, setPrefs] = useState({
    trip_started: true,
    trip_delayed: true,
    trip_completed: true
  });

  const save = () => {
    axios.put("/api/notification-preferences", prefs);
  };

  return (
    <div>
      <h2>Preferences</h2>
      {Object.keys(prefs).map(key => (
        <label key={key}>
          <input
            type="checkbox"
            checked={prefs[key]}
            onChange={() =>
              setPrefs({ ...prefs, [key]: !prefs[key] })
            }
          />
          {key}
        </label>
      ))}
      <button onClick={save}>Save</button>
    </div>
  );
}
