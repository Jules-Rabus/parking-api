import api from "./axios";
import {
  LoginPayload,
  LoginPayloadSchema,
  LoginResponseSchema,
  User,
  UserSchema,
} from "@/types/auth";

export async function login(
  credentials: LoginPayload,
): Promise<{ token: string }> {
  const payload = LoginPayloadSchema.parse(credentials);

  const { data } = await api.post("/login", payload);
  return LoginResponseSchema.parse(data);
}

export async function fetchMe(): Promise<User> {
  const { data } = await api.get("/me");
  return UserSchema.parse(data);
}
