import { z } from "zod";

export const LoginPayloadSchema = z.object({
  email: z.string().email(),
  password: z.string().min(6),
});
export type LoginPayload = z.infer<typeof LoginPayloadSchema>;

export const LoginResponseSchema = z.object({
  token: z.string(),
});

export const UserSchema = z.object({
  id: z.number(),
  email: z.string().email(),
  firstName: z.string().optional(),
  lastName: z.string(),
  roles: z.array(z.string()),
  phones: z.array(z.string()),
  reservations: z.array(z.string()),
});
export type User = z.infer<typeof UserSchema>;

export interface AuthContextType {
  user: User | null;
  login: (credentials: LoginPayload) => Promise<void>;
  logout: () => void;
}
