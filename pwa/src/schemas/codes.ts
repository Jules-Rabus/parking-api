import { z } from "zod";

export const CodeSchema = z.object({
  id: z.number(),
  content: z.string(),
  ajout: z.boolean(),
  startDate: z.string().transform((s) => new Date(s)),
  endDate: z.string().transform((s) => new Date(s)),
  createdAt: z.string().transform((s) => new Date(s)),
  updatedAt: z.string().transform((s) => new Date(s)),
  reservations: z.array(z.string()),
});

export type Code = z.infer<typeof CodeSchema>;

export const CodesCollectionSchema = z
  .object({
    member: z.array(CodeSchema),
  })
  .passthrough();

export type CodesCollection = z.infer<typeof CodesCollectionSchema>;
