# Ashokkumar LoginAsCustomer Module

## 📦 Module Overview

A secure Magento 2 extension that allows authorized Admin users to log in as customers from the Admin Panel with **multi-website support** and full audit traceability.

**Module Name:** `Ashokkumar_LoginAsCustomer`  
**Version:** 1.0.0  
**Magento:** 2.4.4+  
**PHP:** 8.1+  
**License:** Proprietary

### Key Features

✅ **Multi-Website Support** - Login as customer on any website (Ashokkumar, Coverion, etc.)  
✅ **Smart Button Detection** - Automatically shows single button or dropdown based on available websites  
✅ **Grid & Edit Page Access** - Login from customer grid or edit page  
✅ **Cryptographically Secure** - Token-based authentication with SHA-256 hashing  
✅ **Complete Audit Trail** - Track every login attempt with full details  
✅ **ACL Protected** - Granular permission control  
✅ **One-Time Tokens** - Prevents replay attacks  
✅ **Configurable Expiry** - Default 5-minute token lifetime

---

## Security Features

✅ **Cryptographically Secure Tokens** - Uses `random_bytes(32)` for token generation  
✅ **SHA-256 Hash Storage** - Tokens stored as hashes, never plaintext  
✅ **Single-Use Tokens** - Automatically invalidated after first use  
✅ **Configurable Expiry** - Default 5 minutes, prevents stale tokens  
✅ **ACL Protected** - Dual permissions for login action and audit access  
✅ **Complete Audit Trail** - Logs every attempt with admin/customer/IP/timestamp  
✅ **CSRF Protection** - Leverages Magento's form key validation  
✅ **No Password Access** - Bypasses password, uses session-based login  
✅ **IP Tracking** - Records admin IP for forensics  
✅ **Replay Prevention** - Hash comparison prevents token reuse  

---

## 🚀 Installation

1. Copy the module to Magento:

```
app/code/Ashokkumar/LoginAsCustomer
```

2. Run Magento commands:

```bash
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:flush
php bin/magento setup:static-content:deploy
```

---

## ⚙️ Configuration

Navigate to: **Stores > Configuration > Ashokkumar > Login as Customer**

### Settings

| Setting | Description | Default |
|---------|-------------|---------|
| **Enable Module** | Enable/Disable functionality | Yes |
| **Token Lifetime (minutes)** | How long token remains valid | 5 |
| **Redirect Page After Login** | URL path after login | `customer/account` |
| **Enable Audit Logging** | Log all attempts | Yes |

---

## 👥 Permissions Setup

### Grant Permissions to Admin Role

1. Navigate to: **System > Permissions > User Roles**
2. Edit the desired role
3. Under **Role Resources**, expand **Customers**
4. Check:
   - ✅ **Login as Customer > Perform Login as Customer Action**
   - ✅ **Login as Customer > View Audit Log**
5. Under **Stores > Configuration**, check:
   - ✅ **Login as Customer Configuration**
6. Save Role

---

## 📘 Usage Guide

### Method 1: From Customer Grid (Quick Access)

**Single Website Customer:**
1. Navigate to: **Customers > All Customers**
2. Locate customer row
3. Click **Select** in Actions dropdown
4. Click **Login as Customer**
5. New window opens with customer logged in

**Multi-Website Customer:**
1. Navigate to: **Customers > All Customers**
2. Locate customer row
3. Click **Select** in Actions dropdown
4. You'll see multiple options:
   - **Login as Customer (Default)**
   - **Login as Customer (Wholesale)**
   - etc.
5. Click the desired website option
6. New window opens with customer logged into that website

### Method 2: From Customer Edit Page

**Single Website Customer:**
1. Navigate to: **Customers > All Customers**
2. Click **Edit** on a customer
3. Click **Login as Customer** button in header
4. New window opens with customer logged in

**Multi-Website Customer:**
1. Navigate to: **Customers > All Customers**
2. Click **Edit** on a customer
3. Click **Login as Customer ▼** dropdown button in header
4. Select the desired website from dropdown:
   - Default
   - Wholesale
   - etc.
5. New window opens with customer logged into selected website

### Website Selection Logic

The module intelligently detects available websites:
- **Global Customer Sharing** (scope = 0): Shows all websites
- **Per-Website Sharing** (scope = 1): Shows only customer's assigned website
- **Single Website**: Simple button/action
- **Multiple Websites**: Dropdown button/multiple actions

### Viewing Audit Log

1. Navigate to: **Customers > Login as Customer - Audit Log**
2. View grid with columns:
   - Log ID
   - Admin ID / Username
   - Customer ID / Email
   - IP Address
   - Status (Pending/Success/Expired/Failed)
   - Store View
   - Created At / Expires At / Used At
3. Use filters to search by admin, customer, status, date range

---

## 🔍 Security Considerations

### ✅ What We Do

1. **Token Generation:** Cryptographically secure `random_bytes(32)` = 64 hex chars
2. **Token Storage:** Store SHA-256 hash only (64 chars), original token discarded after URL generation
3. **Single-Use:** Token status changed from `pending` > `success` after first use, subsequent attempts rejected
4. **Expiration:** Configurable TTL (default 5 min), server-side timestamp validation
5. **Audit Logging:** Every attempt logged with:
   - Admin ID/username
   - Customer ID/email
   - IP address
   - Timestamp
   - Outcome (success/failed/expired)
6. **ACL Enforcement:** Separate permissions for:
   - Performing login action
   - Viewing audit log
   - Modifying configuration
7. **CSRF Protection:** Magento's built-in form key validation on admin controllers
8. **Session Regeneration:** Customer session ID regenerated after login
9. **No Password Exposure:** Customer password hash never accessed

### ⚠️ Potential Risks

1. **Social Engineering:** Admin with malicious intent could abuse access
   - **Mitigation:** Audit log provides full traceability, restrict ACL to trusted admins only
2. **Token Interception:** If HTTPS not enforced, token could be intercepted
   - **Mitigation:** Always use HTTPS, short expiry window (5 min)
3. **Admin Session Hijacking:** If admin session compromised, attacker could generate tokens
   - **Mitigation:** Enforce admin 2FA, IP whitelisting, regular session timeout

### 🚫 What We DON'T Do

❌ No customer password access  
❌ No plaintext token storage  
❌ No unlimited token lifetime  
❌ No token reuse  
❌ No bypass of ACL permissions  
❌ No modification of customer data during login  

---

## 🐛 Troubleshooting

### Issue: "Login as Customer" button not visible

**Causes:**
1. Module not enabled in config
2. Admin role lacks permission
3. Cache not cleared

**Solution:**
```bash
bin/magento config:set ashokkumar_loginascustomer/general/enabled 1
bin/magento cache:flush
```

### Issue: Token expired error

**Cause:** Token lifetime too short or clock skew

**Solution:**
Increase token lifetime in config (e.g., 10 minutes)

### Issue: Audit log grid empty

**Cause:** Database table not created

**Solution:**
```bash
bin/magento setup:upgrade
bin/magento indexer:reindex
```

### Issue: Permission denied

**Cause:** Admin role not configured

**Solution:**
Grant `Ashokkumar_LoginAsCustomer::login_action` permission to admin role

---

## 🔧 Magento CLI Commands

```bash
# Enable module
bin/magento module:enable Ashokkumar_LoginAsCustomer

# Run setup
bin/magento setup:upgrade

# Compile DI
bin/magento setup:di:compile

# Clear cache
bin/magento cache:clean

# Check module status
bin/magento module:status Ashokkumar_LoginAsCustomer

# Disable module (if needed)
bin/magento module:disable Ashokkumar_LoginAsCustomer
```

---

## 📞 Support
🌐 Multi-Website Implementation

### How It Works

The module automatically detects whether a customer can access multiple websites and adapts the UI accordingly:

**Customer Grid:**
- Single website > One "Login as Customer" action
- Multiple websites > Multiple "Login as Customer (Website Name)" actions

**Customer Edit Page:**
- Single website > Simple button
- Multiple websites > Dropdown button with website options

### Configuration

Check customer account sharing scope:
```bash
php bin/magento config:show customer/account_share/scope
```

- `0` = Global (customers can access all websites)
- `1` = Per Website (customers limited to assigned website)

### Example Scenarios

**Scenario 1: Customer on Default website only**
- Grid: Shows "Login as Customer"
- Edit: Shows simple button
- Logs into Default website

**Scenario 2: Customer on multiple websites (Global sharing)**
- Grid: Shows "Login as Customer (Default)", "Login as Customer (Wholesale)"
- Edit: Shows dropdown with website options
- Admin selects which website to login to