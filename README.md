# E-Ration Service Portal

**E-Ration Service Portal** is a web-based public utility management system designed to eliminate long wait times at local ration distribution centers. The platform provides real-time visibility into stock availability and shop crowd levels while enabling citizens to schedule time slots for efficient, hassle-free ration collection.

---

## ⚙️ Key Features

* **Citizen Dashboard:** Real-time visibility into shop open/close status, current stock levels, and live crowd density.
* **Smart Time-Slot Booking:** Allows citizens to reserve collection slots to prevent overcrowded queues.
* **Shopkeeper & Admin Portal:** Manage inventory, log manual collections, and handle citizen feedback or complaints.
* **Authentication & Collection Simulation:** Built-in role management with simulated biometric collection verification.

---

## 💻 Tech Stack

* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP, MySQL
* **DevOps & Deployment:** Docker, Railway

---

## 🚀 Getting Started

### Local Development Setup

1. **Database Configuration:**
   * Ensure MySQL is running on your machine.
   * Create a database named `erationportal`.
   * Import `database/erationportal.sql` to populate schema and initial sample data.

2. **Run Application Server:**
   ```bash
   php -S localhost:8000
