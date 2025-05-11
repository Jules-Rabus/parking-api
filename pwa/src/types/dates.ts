export interface Date {
  id: number;
  date: string;
  reservations: string[];
  arrivals: string[];
  departures: string[];
  remainingVehicleCapacity: number;
  arrivalVehicleCount: number;
  departureVehicleCount: number;
  createdAt: string;
  updatedAt: string;
}
