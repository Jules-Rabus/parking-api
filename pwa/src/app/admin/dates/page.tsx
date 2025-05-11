// src/app/admin/page.tsx
"use client";

import { getDatesAfter } from "@/api/dates";
import { DateType } from "@/schemas/dates";
import { useEffect, useState } from "react";

export default function Admin() {
  const today = new Date();
  const [dates, setDates] = useState<DateType[]>([]);

  useEffect(() => {
    const fetchDates = async () => {
      try {
        const dates = await getDatesAfter(today);
        setDates(dates);
      } catch (err: any) {
        console.error("Error fetching dates:", err);
      }
    };
    fetchDates();
  }, []);

  const getBgColor = (capacity: number) => {
    if (capacity > 20) return "bg-success";
    if (capacity > 10) return "bg-warning";
    return "bg-error";
  };

  return (
    <div className="min-h-screen p-6">
      <h1 className="text-3xl font-bold mb-4">Dates à venir</h1>

      <div className="overflow-x-auto">
        <table className="table table-zebra w-full">
          <thead>
            <tr>
              <th>Date</th>
              <th>Places restantes</th>
              <th>Arrivées</th>
              <th>Départs</th>
            </tr>
          </thead>
          <tbody>
            {dates.map((d) => {
              const cap = d.remainingVehicleCapacity;
              const bg = getBgColor(cap);
              return (
                <tr key={d.id} className={bg}>
                  <td>
                    {d.date.toLocaleDateString("fr-FR", {
                      weekday: "long",
                      year: "numeric",
                      month: "long",
                      day: "numeric",
                    })}
                  </td>
                  <td className="font-bold">{cap}</td>
                  <td>{d.arrivalVehicleCount}</td>
                  <td>{d.departureVehicleCount}</td>
                </tr>
              );
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
}
