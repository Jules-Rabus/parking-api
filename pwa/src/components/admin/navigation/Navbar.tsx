"use client";

import Link from "next/link";
import { useAuth } from "@/contexts/AuthContext";

export default function Navbar() {
  const { user, logout } = useAuth();

  return (
    <nav>
      <ul>
        <li>
          <Link href="/admin">Dashboard</Link>
        </li>
        <li>
          <Link href="/admin/date">Dates</Link>
        </li>
        <li>
          <Link href="/admin/planning">Planning</Link>
        </li>
      </ul>
      <div>
        {user ? (
          <>
            <span>Bienvenue, {user.email}</span>
            <button onClick={logout}>Déconnexion</button>
          </>
        ) : (
          <Link href="/login">Connexion</Link>
        )}
      </div>
    </nav>
  );
}
