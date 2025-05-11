"use client";

import api from "./axios";
import { DatesCollectionSchema, DateType } from "@/schemas/dates";

export async function getDates(): Promise<DateType[]> {
  const { data } = await api.get<DateType[]>("/dates");
  return data;
}

export async function getDatesAfter(date: Date): Promise<DateType[]> {
  const { data } = await api.get("/dates", {
    params: {
      "date[strictly_after]": date.toISOString(),
      "order[date]": "asc",
    },
  });
  console.log("Dates after", date, data);

  const { member } = DatesCollectionSchema.parse(data);
  return member;
}

export async function getDatesBefore(date: Date): Promise<DateType[]> {
  const { data } = await api.get<DateType[]>("/dates", {
    params: {
      "date[strictly_before]": date.toISOString(),
      "order[date]": "desc",
    },
  });
  return data;
}

export async function getDatesBetween(
  startDate: Date,
  endDate: Date,
): Promise<DateType[]> {
  const { data } = await api.get<DateType[]>("/dates", {
    params: {
      "date[strictly_after]": startDate.toISOString(),
      "date[strictly_before]": endDate.toISOString(),
    },
  });
  return data;
}
