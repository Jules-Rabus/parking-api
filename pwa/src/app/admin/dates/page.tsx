"use client";
import { getDatesAfter } from "@/api/dates";
import { DateType } from "@/schemas/dates";
import { useEffect, useState, useRef, useCallback } from "react";
import { MonthSeparator } from "@/app/admin/dates/components/MonthSperator";
import { DateRow } from "@/app/admin/dates/components/DateRow";
import { MonthWeekMinGrid } from "@/app/admin/dates/components/MonthWeekGrid";

const PAGE_SIZE = 50;

export default function Admin() {
  const [dates, setDates] = useState<DateType[]>([]);
  const [loading, setLoading] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [page, setPage] = useState(1);
  const [startDate] = useState(new Date());
  const observer = useRef<IntersectionObserver | null>(null);
  const sentinelRef = useRef<HTMLDivElement>(null);

  const loadMore = useCallback(async () => {
    if (loading || !hasMore) return;
    setLoading(true);
    try {
      const { member, view } = await getDatesAfter(startDate, page, PAGE_SIZE);
      setDates((prev) => [...prev, ...member]);
      setHasMore(Boolean(view && view.next));
      setPage((prev) => prev + 1);
    } finally {
      setLoading(false);
    }
  }, [loading, hasMore, page, startDate]);

  useEffect(() => {
    loadMore();
  }, []);

  useEffect(() => {
    if (loading) return;
    if (!sentinelRef.current) return;
    if (observer.current) observer.current.disconnect();
    observer.current = new window.IntersectionObserver(([entry]) => {
      if (entry.isIntersecting && hasMore && !loading) {
        loadMore();
      }
    });
    observer.current.observe(sentinelRef.current);
  }, [loadMore, loading, hasMore]);

  return (
    <div className="min-h-screen p-6">
      <MonthWeekMinGrid />
      <div className="overflow-x-auto">
        <table className="table w-full">
          <thead>
            <tr>
              <th>Date</th>
              <th>Place</th>
              <th>Revenues</th>
            </tr>
          </thead>
          <tbody>
            {dates.map((date) => {
              const isFirstOfMonth = date.date.getDate() === 1;
              const month = date.date.toLocaleDateString("fr-FR", {
                month: "long",
                year: "numeric",
              });
              return (
                <>
                  {isFirstOfMonth && (
                    <MonthSeparator month={month} key={"m-" + date.id} />
                  )}
                  <DateRow date={date} key={date.id} />
                </>
              );
            })}
          </tbody>
        </table>
        <div ref={sentinelRef} className="h-8 flex justify-center items-center">
          {loading && (
            <span className="loading loading-spinner loading-lg text-primary"></span>
          )}
          {!hasMore && (
            <span className="text-base text-gray-400">
              Plus de dates à charger.
            </span>
          )}
        </div>
      </div>
    </div>
  );
}
