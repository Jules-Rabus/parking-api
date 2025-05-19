"use client";

import api from "./axios";
import { DatesCollectionSchema, DateType } from "@/schemas/dates";

export async function getDates(): Promise<DateType[]> {
  const { data } = await api.get<DateType[]>("/dates");
  return data;
}

export async function getDatesAfter(
  date: Date,
  page: number,
  pageSize: number,
) {
  const { data } = await api.get("/dates", {
    params: {
      "date[strictly_after]": date.toISOString(),
      "order[date]": "asc",
      page: page,
      itemsPerPage: pageSize,
    },
  });
  const { member, view } = DatesCollectionSchema.parse(data);
  return { member, view };
}

export async function getDatesBefore(
  date: Date,
  page: number,
  pageSize: number,
) {
  const { data } = await api.get("/dates", {
    params: {
      "date[strictly_before]": date.toISOString(),
      "order[date]": "desc",
      page: page,
      itemsPerPage: pageSize,
    },
  });
  const { member, view } = DatesCollectionSchema.parse(data);
  return { member, view };
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
