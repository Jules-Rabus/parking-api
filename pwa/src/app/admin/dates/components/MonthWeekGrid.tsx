import { useState, useEffect, FC } from "react";
import { getDatesAfter } from "@/api/dates";
import { DateType } from "@/schemas/dates";

type MonthlyWeeklyData = {
  monthLabel: string;
  year: number;
  weeklyCapacities: number[][];
};

export const getBackgroundColor = (capacity: number): string => {
  if (capacity > 20) return "bg-green-500 text-white";
  if (capacity > 10) return "bg-orange-400 text-black";
  return "bg-red-500 text-white";
};

export const getMonthName = (date: Date): string =>
  date.toLocaleDateString("fr-FR", { month: "short" });

export const getWeekNumber = (date: Date): number => {
  const utcDate = new Date(
    Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()),
  );
  const dayOfWeek = utcDate.getUTCDay() || 7;
  utcDate.setUTCDate(utcDate.getUTCDate() + 4 - dayOfWeek);
  const yearStart = new Date(Date.UTC(utcDate.getUTCFullYear(), 0, 1));
  const weekNumber = Math.ceil(
    ((utcDate.getTime() - yearStart.getTime()) / 86400000 + 1) / 7,
  );
  return weekNumber;
};

export const getWeeklyMinimumsForMonth = (
  datesInMonth: DateType[],
): number[][] => {
  if (datesInMonth.length === 0) return [];
  const initialWeekIndex = getWeekNumber(datesInMonth[0].date);
  const initialState = {
    currentWeekIndex: initialWeekIndex,
    currentWeekCapacities: [datesInMonth[0].remainingVehicleCapacity],
    weeklyCapacities: [] as number[][],
  };

  const reduced = datesInMonth.slice(1).reduce((state, dateItem) => {
    const weekIndex = getWeekNumber(dateItem.date);
    if (weekIndex !== state.currentWeekIndex) {
      state.weeklyCapacities = [
        ...state.weeklyCapacities,
        state.currentWeekCapacities,
      ];
      state.currentWeekCapacities = [];
      state.currentWeekIndex = weekIndex;
    }
    state.currentWeekCapacities = [
      ...state.currentWeekCapacities,
      dateItem.remainingVehicleCapacity,
    ];
    return state;
  }, initialState);

  return [...reduced.weeklyCapacities, reduced.currentWeekCapacities];
};

export const groupUpcomingMonthsByWeek = (
  allDates: DateType[],
  numberOfMonths: number,
): MonthlyWeeklyData[] => {
  const groups = allDates.reduce<Record<string, DateType[]>>(
    (acc, dateItem) => {
      const key = `${dateItem.date.getFullYear()}-${dateItem.date.getMonth()}`;
      acc[key] = acc[key] ? [...acc[key], dateItem] : [dateItem];
      return acc;
    },
    {},
  );

  return Object.values(groups)
    .map((datesForMonth) => ({
      monthLabel: getMonthName(datesForMonth[0].date),
      year: datesForMonth[0].date.getFullYear(),
      weeklyCapacities: getWeeklyMinimumsForMonth(datesForMonth),
    }))
    .slice(0, numberOfMonths);
};

export const MonthWeekMinGrid: FC = () => {
  const [upcomingDates, setUpcomingDates] = useState<DateType[]>([]);

  useEffect(() => {
    const now = new Date();
    const firstDayOfCurrentMonth = new Date(
      now.getFullYear(),
      now.getMonth(),
      1,
    );
    const maxDaysToFetch = 31 * 5;

    getDatesAfter(firstDayOfCurrentMonth, 1, maxDaysToFetch)
      .then(({ member }) => setUpcomingDates(member))
      .catch(() => {});
  }, []);

  const groupedData = groupUpcomingMonthsByWeek(upcomingDates, 5);

  return (
    <div className="overflow-x-auto max-w-lg">
      <table className="table table-compact border border-neutral w-auto">
        <thead>
          <tr>
            <th className="bg-neutral text-white">L-D</th>
            {[1, 2, 3, 4, 5].map((weekNumber) => (
              <th key={weekNumber} className="bg-neutral text-white">
                {weekNumber}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {groupedData.map(({ monthLabel, weeklyCapacities }, rowIndex) => (
            <tr key={rowIndex} className="font-bold">
              <td className="bg-neutral text-white">{monthLabel}.</td>
              {weeklyCapacities.map((capacities, weekIndex) => {
                const minimumCapacity = Math.min(...capacities);
                return (
                  <td
                    key={weekIndex}
                    className={getBackgroundColor(minimumCapacity)}
                  >
                    {minimumCapacity}
                  </td>
                );
              })}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
