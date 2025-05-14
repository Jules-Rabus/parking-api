"use client";

import {getReservations} from "@/api/reservations";
import { ReservationType } from "@/schemas/reservations";
import { useEffect, useState } from "react";

export default function Admin() {
  // const today = new Date();
  const [reservations, setReservations] = useState<ReservationType[]>([]);

  useEffect(() => {
    const fetchReservations = async () => {
      try {
        const reservations = await getReservations();
        setReservations(reservations);
      } catch (err: any) {
        console.error("Error fetching reservations:", err);
      }
    };
    fetchReservations();
  }, []);

  return (
    <div className="min-h-screen p-6">
      <h1 className="text-3xl font-bold mb-4">Réservations</h1>

      <div className="overflow-x-auto">
        <table className="table table-zebra w-full">
          <thead>
          <tr>
            <th>Date</th>
            <th>Places</th>
            <th>?</th>
          </tr>
          </thead>
          <tbody>
          {reservations.map((d) => {
            return (
              <tr key={d.id}>
                <td>{d.vehicleCount}</td>
                <td>
                  {d.startDate.toLocaleDateString("fr-FR", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                  })} -
                  {d.endDate.toLocaleDateString("fr-FR", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                  })}
                </td>
                <td>{d.vehicleCount}</td>
                <td></td>
              </tr>
            );
          })}
          </tbody>
        </table>
      </div>
    </div>
  );
}

