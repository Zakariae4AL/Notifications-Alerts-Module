import { afterEach, describe, expect, it, vi } from "vitest";
import client, { errorMessage, LOCALE_KEY, TOKEN_KEY } from "./client";

const requestInterceptor = client.interceptors.request.handlers[0].fulfilled;
const responseErrorInterceptor = client.interceptors.response.handlers[0].rejected;

describe("api client", () => {
  afterEach(() => vi.restoreAllMocks());

  it("sends the bearer token and the current language", () => {
    localStorage.setItem(TOKEN_KEY, "abc123");
    localStorage.setItem(LOCALE_KEY, "fr");

    const config = requestInterceptor({ headers: {} });

    expect(config.headers.Authorization).toBe("Bearer abc123");
    expect(config.headers["Accept-Language"]).toBe("fr");
  });

  it("sends no Authorization header when logged out", () => {
    const config = requestInterceptor({ headers: {} });
    expect(config.headers.Authorization).toBeUndefined();
    expect(config.headers["Accept-Language"]).toBe("en");
  });

  it("clears the token and broadcasts an event on 401", async () => {
    localStorage.setItem(TOKEN_KEY, "expired");
    const listener = vi.fn();
    window.addEventListener("auth:expired", listener);

    await expect(responseErrorInterceptor({ response: { status: 401 } })).rejects.toBeDefined();

    expect(localStorage.getItem(TOKEN_KEY)).toBeNull();
    expect(listener).toHaveBeenCalledOnce();
    window.removeEventListener("auth:expired", listener);
  });

  it("keeps the token on other errors", async () => {
    localStorage.setItem(TOKEN_KEY, "valid");
    await expect(responseErrorInterceptor({ response: { status: 500 } })).rejects.toBeDefined();
    expect(localStorage.getItem(TOKEN_KEY)).toBe("valid");
  });

  it("extracts the first Laravel validation message", () => {
    const error = { response: { data: { message: "Invalid", errors: { email: ["Bad credentials."] } } } };
    expect(errorMessage(error, "fallback")).toBe("Bad credentials.");
    expect(errorMessage({ response: { data: { message: "Server says no" } } }, "fallback")).toBe("Server says no");
    expect(errorMessage({}, "fallback")).toBe("fallback");
  });
});
