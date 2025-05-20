"use client";

import api from "./axios";
import { Code } from "@/schemas/codes";
import { CodesCollectionSchema } from "@/schemas/codes";

export async function getCodes(): Promise<Code[]> {
  const { data } = await api.get<Code[]>("/reservations");
  return data;
}

export async function getCodesByDate(
  date: Date,
): Promise<Code[]> {
  const { data } = await api.get("/codes", {
    params: {
      "startDate[strictly_after]": date.toISOString(),
      "order[endDate]": "asc",
    },
  });

  const { member } = CodesCollectionSchema.parse(data);
  return member;
}
