import type { Metadata } from "next";
import "./globals.css";
import { AuthProvider } from "@/contexts/AuthContext";

export const metadata: Metadata = {
  title: "Parking Du Moulin",
  description: "Application de gestion de parking",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="fr" className="min-h-screen flex flex-col">
      <body>
        <AuthProvider>{children}</AuthProvider>
      </body>
    </html>
  );
}
