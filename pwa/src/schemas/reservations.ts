import { z } from "zod";
import {MessageSchema} from "@/schemas/message";
import {UserSchema} from "@/schemas/auth";

export const ReservationSchema = z.object({
  id: z.number(),
  startDate: z.string().transform((s) => new Date(s)),
  endDate: z.string().transform((s) => new Date(s)),
  createdAt: z.string().transform((s) => new Date(s)),
  updatedAt: z.string().transform((s) => new Date(s)),
  vehicleCount: z.number(),
  bookingDate: z.string().transform((s) => new Date(s)),
  dates: z.array(z.string()),
  message: MessageSchema,
  holder: UserSchema
});

export type ReservationType = z.infer<typeof ReservationSchema>;

export const ReservationsCollectionSchema = z
  .object({
    member: z.array(ReservationSchema),
  })
  .passthrough();

export type ReservationsCollection = z.infer<typeof ReservationsCollectionSchema>;
