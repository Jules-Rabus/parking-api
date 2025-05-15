export const MonthSeparator = ({ month }: { month: string }) => {
  return (
    <tr>
      <td
        colSpan={3}
        className="text-neutral-content text-xl font-bold text-center"
      >
        {month.charAt(0).toUpperCase() + month.slice(1)}
      </td>
    </tr>
  );
};
