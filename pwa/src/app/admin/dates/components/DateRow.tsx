import { DateType } from "@/schemas/dates";

export const DateRow = ({ date }: { date: DateType }) => {
  const getBgColor = (capacity: number) => {
    if (capacity > 20) return "bg-success";
    if (capacity > 10) return "bg-warning";
    return "bg-error";
  };
  const bg = getBgColor(date.remainingVehicleCapacity);
  return (
    <tr className={bg}>
      <td className="capitalize">
        {date.date.toLocaleDateString("fr-FR", {
          weekday: "long",
          year: "numeric",
          month: "long",
          day: "numeric",
        })}
      </td>
      <td>{date.remainingVehicleCapacity}</td>
      <td className="flex justify-between">
        <span
          className={date.arrivalVehicleCount > 5 ? "bg-success" : "bg-error"}
        >
          {date.arrivalVehicleCount}
        </span>
        <span
          className={date.departureVehicleCount > 5 ? "bg-red-200" : "bg-error"}
        >
          {date.departureVehicleCount}
        </span>
      </td>
    </tr>
  );
};
