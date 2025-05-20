"use client";

import {getCodesByDate} from "@/api/codes";
import { Code } from "@/schemas/codes";
import { useEffect, useState } from "react";

export default function Admin() {

  const [codes, setCodes] = useState<Code[]>([]);
  const [startDate] = useState(new Date());

  useEffect(() => {
    const fetchCodes = async () => {
      try {
        const codes = await getCodesByDate(startDate);
        setCodes(codes);
      } catch (err: any) {
        console.error("Error fetching codes:", err);
      }
    };
    fetchCodes();
  }, []);

  return (
    <div className="min-h-screen p-6">
      <h1 className="text-3xl font-bold mb-4">Codes</h1>

      <div className="overflow-x-auto">
        <table className="table table-zebra w-full">
          <thead>
          <tr>
            <th>Début</th>
            <th>Fin</th>
            <th>Code</th>
          </tr>
          </thead>
          <tbody>
          {codes.map((d) => {
            return (
              <tr key={d.id}>
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
                <td>{d.content}</td>
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

