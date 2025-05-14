"use client";

import api from "./axios";
import {ReservationType} from "@/schemas/reservations";
import {ReservationsCollectionSchema} from "@/schemas/reservations";

export async function getReservations(): Promise<ReservationType[]> {
  const { data } = await api.get<ReservationType[]>("/reservations");
  return data;
}

export async function getReservationsByDate(date: Date): Promise<ReservationType[]> {
  const { data } = await api.get("/reservations", {
    params: {
      "startDate[strictly_after]": date.toISOString(),
      "order[endDate]": "asc",
    },
  });
  console.log("Reservations dates: ", date, data);

  const { member } = ReservationsCollectionSchema.parse(data);
  return member;
}
