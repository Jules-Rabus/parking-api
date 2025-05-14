import {z} from "zod";
import {MessageStatus} from "@/schemas/enum/messageStatus";
import {PhoneSchema} from "@/schemas/phone";

export const MessageSchema = z.object({
  id: z.number(),
  content: z.string(),
  answerContent: z.string(),
  status: z.nativeEnum(MessageStatus),
  batches: z.array(z.string()),
  phone: PhoneSchema,
  email: z.string().email()
});

export type MessageType = z.infer<typeof MessageSchema>;

export const MessagesCollectionSchema = z
  .object({
    member: z.array(MessageSchema),
  })
  .passthrough();

export type MessagesCollection = z.infer<typeof MessagesCollectionSchema>;
