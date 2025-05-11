"use client";

import Link from "next/link";

export default function AdminHeader() {
  return (
    <header className="">
      <nav className="dock">
        <Link href="/admin" className="docker-label">
          <span className="btn btn-ghost">Dashboard</span>
        </Link>
        <Link href="/admin/planning" className="docker-label">
          <span className="btn btn-ghost">Planning</span>
        </Link>
        <Link href="/admin/dates" className="docker-label">
          <span className="btn btn-ghost">Dates</span>
        </Link>
      </nav>
    </header>
  );
}
