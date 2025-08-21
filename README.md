# CS-Cart Click2Pay Payment Integration

This repository provides a custom **Click2Pay payment method integration** for **CS-Cart**.
The module enables secure online payments via Click2Pay directly from your CS-Cart store checkout.

---

## 🚀 Features

* Seamless integration with **Click2Pay API**.
* Secure **payment redirection** and callback handling.
* Supports **order validation** and **status synchronization**.
* Configurable settings from **CS-Cart admin panel**.
* Error logging for troubleshooting.

---

## 📦 Installation

1. **Upload the addon**

   * Copy the addon folder (e.g. `click2pay/`) into your CS-Cart installation under:

     ```
     app/addons/
     ```

2. **Install from Admin Panel**

   * Go to **Add-ons → Manage add-ons**.
   * Locate **Click2Pay Payment Gateway** and click **Install**.

3. **Activate Payment Method**

   * Navigate to **Administration → Payment methods → Add payment method**.
   * Choose **Click2Pay** as the processor.
   * Fill in required API credentials.

---

## ⚙️ Configuration

* **API Key**: Provided by Click2Pay.
* **Merchant ID**: Your Click2Pay merchant account ID.
* **Callback URL**: Should point to your CS-Cart notification handler:

  ```
  https://yourdomain.com/index.php?dispatch=payment_notification.notify&payment=click2pay
  ```
* **Test Mode**: Enable for sandbox transactions.

---

## 🔄 Workflow

1. Customer selects **Click2Pay** at checkout.
2. They are redirected to the Click2Pay secure payment page.
3. After payment, Click2Pay calls the **notification callback**.
4. CS-Cart verifies payment and updates **order status**.

---

## 🐞 Troubleshooting

* **Payment not updating?**

  * Ensure **callback URL** is correctly set in Click2Pay dashboard.
  * Check CS-Cart logs: `var/logs/`.

* **Invalid signature?**

  * Verify your **API Key** and **Merchant ID**.
  * Ensure server time is synchronized.

---

## 📖 References

* [CS-Cart Developer Docs](https://docs.cs-cart.com/latest/)
* [Click2Pay API Documentation](https://click2pay.example/api-docs) *(replace with real link)*

---

## 📜 License

This integration is released under the **MIT License**.

