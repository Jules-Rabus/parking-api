import {z} from "zod";
import {UserSchema} from "@/schemas/auth";

export const PhoneSchema = z.object({
  id: z.number(),
  phoneNumber: z.string(),
  owner: UserSchema,
  messages: z.array(z.string()),
});

export type PhoneType = z.infer<typeof PhoneSchema>;

export const PhonesCollectionSchema = z
  .object({
    member: z.array(PhoneSchema),
  })
  .passthrough();

export type PhonesCollection = z.infer<typeof PhonesCollectionSchema>;
