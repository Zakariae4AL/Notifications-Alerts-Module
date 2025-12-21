import { BrowserRouter, Routes, Route, Link } from "react-router-dom";
import Notifications from "./pages/Notifications";
import Preferences from "./pages/Preferences";

function App() {
  return (
    <BrowserRouter>
      <nav>
        <Link to="/notifications">Notifications</Link> |{" "}
        <Link to="/preferences">Preferences</Link>
      </nav>

      <Routes>
        <Route path="/notifications" element={<Notifications />} />
        <Route path="/preferences" element={<Preferences />} />
      </Routes>
    </BrowserRouter>
  );
}

export default App;

