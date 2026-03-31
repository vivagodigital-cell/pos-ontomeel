# Ontomeel Library & POS Management System

Welcome to the **Ontomeel Library & POS** documentation. This document is written from a client perspective to give you a complete understanding of the system's workflow, features, and capabilities. 

This modern platform acts as your daily operational hub. It is specifically designed to handle both **traditional library workflows** (borrowing, tracking, and returning books) alongside standard **Point-of-Sale (POS) operations** (selling books, stationery, flowers, accessories, etc.).

---

## 1. Dashboard
The Dashboard is your control center. The moment you log in, it provides a high-level overview of your business snapshot:
- **Quick Statistics**: See total members, gross sales, active borrowings, and out-of-stock items at a glance.
- **Overdue Alerts**: Instantly flags items that members have not returned by their due date.
- **Recent Activity**: A feed showing the latest transactions, adding transparency to daily operations.

---

## 2. General Inventory
The Inventory module is where your entire catalog lives. The system smartly separates "Books" from "General Items" behind the scenes while keeping your user interface unified and easy to use.

### Facilities & Options:
- **Smart Add/Edit Form**: When adding an item, simply select its category (Books, Stationary, Flowers, Accessories, or General). If it's a Book, you get detailed fields (Author, Publisher, Cover Format, Genre). If it's a non-book item, the form shrinks to only ask for the essentials (Title, Stock, Price, Supplier).
- **Stock Tracking & Alerts**: Tracks exact stock quantities and flags items when they drop below your specified "Minimum Stock Level."
- **One-Click Restock**: A quick "+" button on every item card to instantly add newly arrived stock without opening the full edit menu.
- **Powerful Search & Filters**: Look up any item instantly using its Bengali name, English name, or Barcode/ISBN. Use quick-filter pills to view only Stationary, Books, or Flowers with a single click.

---

## 3. The Terminal (Point-of-Sale)
Designed for speed and ease of use at the checkout counter. The Terminal splits into a visual **Product Browser** and a **Smart Cart**.

### Facilities & Options:
- **Dual-Mode Actions (Sell vs. Issue)**: 
  - **Sell**: Standard retail checkout for immediate purchase of goods.
  - **Issue**: For library items that are being borrowed. The system will prompt you for a Due Date and link the items to a specific Member's account.
- **Search capabilities**: You can type an item's SKU/Barcode or its partial name into the global search bar, and it will immediately add it to the cart.
- **Guest vs. Member Checkout**: You can process a quick guest sale (no name attached) or assign the transaction/borrowing to a registered Member from the database.
- **Receipt Printing**: Built-in, automatic formatting for standard 80mm thermal receipt printers immediately after a successful checkout.
- **Cart Controls**: Adjust quantities on the fly, remove accidental items, or empty the cart entirely in one click.

---

## 4. Suppliers & Purchases
This module ensures transparent accounting and keeps your inventory continuously synced with your real-world buying habits.

### Facilities & Options:
- **Supplier Address Book**: Keep track of vendors and their contact details.
- **Multi-Item Purchase Records**: Log a single invoice featuring multiple items (e.g., 10 pens, 5 notebooks, and 3 specific books). 
- **Automated Inventory Syncing**: When you save a purchase record, the system automatically creates new database entries for new items or updates stock levels for existing items. It perfectly routes Books into the Book directory and everything else into the General Inventory directory.
- **Ledger & Due Tracking**: Input how much you owe the supplier versus how much you actually paid today. The system keeps a running balance of your debts.
- **Record Payments**: A financial tool to log whenever you hand over cash or make a bank transfer to clear a supplier's previous credit balance.

---

## 5. Members Management
Your CRM (Customer Relationship Management) area, tailored heavily around library interactions.

### Facilities & Options:
- **Member Directory**: View all registered students/readers.
- **Borrowing Timelines**: Click into any member's profile to see what books they currently hold, what they have successfully returned, and what is currently overdue.
- **Return Processing**: Easily click to "Release/Return" an item that a member brings back, injecting the book instantly back into available inventory.
- **Enforced Verification**: At the member's login (member portal), the system enforces them to verify their email address via OTP (One Time Password) to keep communication channels pure.
- **Data Import**: Tools capable of bulk-importing member directories so you don't have to add hundreds of students manually.

---

## 6. Real-Time Reports
No system is complete without analytics. The Reports page transforms your raw data into actionable insights:
- **Financial Breakdowns**: Daily, weekly, and monthly sales summaries to check profitability.
- **Inventory Valuations**: Understand the exact monetary value of the goods sitting on your shelves right now.
- **Popularity Tracking**: Identify your most borrowed books or best-selling retail items to make smarter buying decisions.

---

## Summary of the Daily Workflow
1. A shipment arrives -> **Go to Suppliers**, log the purchase invoice. Stock is updated automatically.
2. A student walks in to buy a pen -> **Go to Terminal**, search for the pen, select "Guest", collect cash, and click **Sell**.
3. A student wishes to open an account -> **Go to Members**, add their details. They will log in and verify their email.
4. A student borrows a book -> **Go to Terminal**, add the book to cart, select the student's name, choose a Due Date, and click **Issue**.
5. The student returns the book a week later -> **Go to Members** or **Terminal**, locate their active borrowing, and click **Return**. Stock goes back up automatically.
6. At the end of the day -> **Go to Reports** and review total revenue and outstanding supplier dues.

---

## License & Copyright

**© 2026 VIVAGO DIGITAL. All rights reserved.**

This project is a bespoke client application developed specifically for **Ontomeel** by **VIVAGO DIGITAL**.

The source code in this repository is provided strictly for **education and research** purposes.

Under this protective license, you are **NOT permitted** to:
1. Clone, fork, or duplicate this repository.
2. Directly copy, modify, or distribute the code, whether in whole or in part.
3. Use the code for commercial or personal projects.

Any unauthorized commercial use, reproduction, or distribution of this software is strictly prohibited. For inquiries regarding usage rights or commercial applications, please refer to VIVAGO DIGITAL.
