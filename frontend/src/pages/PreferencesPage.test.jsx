import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { notificationsApi, preferencesApi } from "../api";
import { LOCALE_KEY } from "../api/client";
import { preferences } from "../test/fixtures";
import { renderPage } from "../test/utils";
import PreferencesPage from "./PreferencesPage";

vi.mock("../api");

describe("PreferencesPage", () => {
  beforeEach(() => {
    vi.resetAllMocks();
    localStorage.setItem(LOCALE_KEY, "en");
    notificationsApi.unreadCount.mockResolvedValue(0);
    preferencesApi.get.mockResolvedValue(preferences);
  });

  it("shows the current preference matrix", async () => {
    renderPage(<PreferencesPage />);

    expect(await screen.findByLabelText("Trip started — In-app")).toBeChecked();
    expect(screen.getByLabelText("Trip started — Email")).not.toBeChecked();
    expect(screen.getByLabelText("Passenger absent — Email")).toBeChecked();
  });

  it("saves the toggled preferences", async () => {
    preferencesApi.save.mockImplementation(async (rows) => ({
      message: "Your notification preferences have been saved.",
      data: preferences.map((p) => ({ ...p, ...rows.find((r) => r.type === p.type) })),
    }));
    renderPage(<PreferencesPage />);

    await userEvent.click(await screen.findByLabelText("Trip started — Email"));
    await userEvent.click(screen.getByRole("button", { name: "Save preferences" }));

    expect(preferencesApi.save).toHaveBeenCalledWith([
      { type: "trip_started", in_app: true, email: true },
      { type: "passenger_absent", in_app: true, email: true },
    ]);
    expect(await screen.findByRole("status")).toHaveTextContent("Your notification preferences have been saved.");
    expect(screen.getByLabelText("Trip started — Email")).toBeChecked();
  });

  it("reports a save error", async () => {
    preferencesApi.save.mockRejectedValue({ response: { status: 500 } });
    renderPage(<PreferencesPage />);

    await userEvent.click(await screen.findByRole("button", { name: "Save preferences" }));

    expect(await screen.findByRole("alert")).toHaveTextContent("Could not save your preferences.");
  });

  it("reports a load error", async () => {
    preferencesApi.get.mockRejectedValue(new Error("down"));
    renderPage(<PreferencesPage />);

    expect(await screen.findByRole("alert")).toHaveTextContent("Could not load your preferences.");
  });
});
