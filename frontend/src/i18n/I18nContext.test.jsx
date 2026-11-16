import { act, renderHook } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { LOCALE_KEY } from "../api/client";
import { I18nProvider, useI18n } from "./I18nContext";
import messages from "./messages";

const wrapper = ({ children }) => <I18nProvider>{children}</I18nProvider>;

describe("i18n", () => {
  it("has the same keys in English and French", () => {
    expect(Object.keys(messages.fr).sort()).toEqual(Object.keys(messages.en).sort());
  });

  it("translates and interpolates", () => {
    localStorage.setItem(LOCALE_KEY, "en");
    const { result } = renderHook(() => useI18n(), { wrapper });

    expect(result.current.t("inbox.unread", { count: 3 })).toBe("3 unread");
    expect(result.current.t("unknown.key")).toBe("unknown.key");
  });

  it("switches language and remembers it", () => {
    localStorage.setItem(LOCALE_KEY, "en");
    const { result } = renderHook(() => useI18n(), { wrapper });

    act(() => result.current.setLocale("fr"));

    expect(result.current.locale).toBe("fr");
    expect(result.current.t("nav.preferences")).toBe("Préférences");
    expect(localStorage.getItem(LOCALE_KEY)).toBe("fr");
    expect(document.documentElement.lang).toBe("fr");
  });

  it("ignores unsupported languages", () => {
    localStorage.setItem(LOCALE_KEY, "en");
    const { result } = renderHook(() => useI18n(), { wrapper });

    act(() => result.current.setLocale("de"));

    expect(result.current.locale).toBe("en");
  });
});
