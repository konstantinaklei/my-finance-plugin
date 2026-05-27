# My Finance 
Contributors: konstantinaklei
Tags: finance, data visualization, reporting, dashboard, analytics
Stable tag: 1.0.0
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

## Description 

A professional, data-driven WordPress plugin engineered for financial management and analytics. My Finance transforms raw transactional data into actionable insights through dynamic dashboards and automated reporting. 

Designed with Clean Architecture principles, the plugin securely handles data entry.

## Features

* Data Visualization: Integrates Chart.js to render interactive Cash Flow Bar Charts, providing a clear visual representation of income versus expenses over time.
* Interactive Analytics: Features an asynchronous, AJAX-powered dashboard utilizing the Fetch API. Users can apply complex filters (by custom date ranges and transaction types) to instantly query and sort data without page reloads.
* Automated Reporting: Leverages WP-Cron to automate background tasks, calculating daily metrics and dispatching automated summary reports via email.
* Structured Data Management: Utilizes Custom Post Types (CPTs) and metadata to maintain a clean, structured, and scalable database architecture for financial records.
* Global Standards (i18n): The entire codebase is translation-ready, ensuring scalability and compliance with internationalization standards.

## Installation 

### Minimum Requirements 
* **WordPress:** Version 6.0 or higher
* **PHP:** Version 8.3 or higher
* **Browser:** Modern browser with JavaScript enabled (required for Chart.js rendering and Fetch API)

## Setup Instructions 
1. Upload the `my-finance-plugin` directory to your `/wp-content/plugins/` folder via FTP or the WordPress plugin uploader.
2. Navigate to the 'Plugins' menu in your WordPress admin area.
3. Click 'Activate' next to the My Finance plugin.
4. A new 'Transactions' (or translated equivalent) menu will appear in the admin sidebar for data entry.

## Usage & Shortcodes 

Deploy your analytics dashboards anywhere on your site using the following shortcodes:

* `[finance_dashboard]` : Renders the primary interactive data table with real-time filtering tools and total balance calculations.
* `[finance_stats]` : Generates the interactive monthly cash flow chart (Chart.js) for visual data analysis.

## Changelog 

### 1.0.0
* **Initial release:** Implemented OOP architecture, AJAX filtering, Chart.js integrations, and WP-Cron automated reporting.

= 1.0.0 =
* Initial release: Implemented OOP architecture, AJAX filtering, Chart.js integrations, and WP-Cron automated reporting.
