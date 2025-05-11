"use client";

import AdminHeader from "@components/admin/header/Header";

export default function AdminLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <div className="min-h-screen">
      <AdminHeader />
      <main className="p-6">{children}</main>
    </div>
  );
}
