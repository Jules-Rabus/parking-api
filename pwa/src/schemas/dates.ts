import { z } from "zod";

export const DateTypeSchema = z.object({
  id: z.number(),
  date: z.string().transform((s) => new Date(s)),
  createdAt: z.string().transform((s) => new Date(s)),
  updatedAt: z.string().transform((s) => new Date(s)),
  reservations: z.array(z.string()),
  arrivals: z.array(z.string()),
  departures: z.array(z.string()),
  remainingVehicleCapacity: z.number(),
  arrivalVehicleCount: z.number(),
  departureVehicleCount: z.number(),
});

export type DateType = z.infer<typeof DateTypeSchema>;

export const DatesCollectionSchema = z
  .object({
    member: z.array(DateTypeSchema),
  })
  .passthrough();

export type DatesCollection = z.infer<typeof DatesCollectionSchema>;
