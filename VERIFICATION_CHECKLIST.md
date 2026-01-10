# ✅ Module Installation Verification Checklist

## Pre-Installation Verification

Run these checks **BEFORE** installing:

```bash
# 1. Verify module files exist
ls -la /var/www/html/ashokkumar.io/project/app/code/Ashokkumar/LoginAsCustomer/

# 2. Count files (should be 30)
find /var/www/html/ashokkumar.io/project/app/code/Ashokkumar/LoginAsCustomer -type f | wc -l

# 3. Check registration.php exists
cat /var/www/html/ashokkumar.io/project/app/code/Ashokkumar/LoginAsCustomer/registration.php
```

**Expected:** 30 files, registration.php shows ComponentRegistrar

---

## Installation Commands

```bash
cd /var/www/html/ashokkumar.io/project

# Option 1: Automated (Recommended)
./install-login-as-customer.sh

# Option 2: Manual
bin/magento module:enable Ashokkumar_LoginAsCustomer
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

---

## Post-Installation Verification

### 1. Module Enabled Check
```bash
bin/magento module:status Ashokkumar_LoginAsCustomer
```
**Expected Output:**
```
Module is enabled
```

### 2. Database Table Check
```bash
bin/magento db:status
# OR manually:
mysql -u your_user -p your_database -e "SHOW TABLES LIKE 'ashokkumar_login_as_customer_log';"
```
**Expected:** Table exists with 12 columns

### 3. Configuration Check
```bash
bin/magento config:show ashokkumar_loginascustomer/general/enabled
```
**Expected:** `1` (enabled)

### 4. ACL Check
```bash
bin/magento acl:resources | grep -i ashokkumar
```
**Expected Output:**
```
Ashokkumar_LoginAsCustomer::login_as_customer
Ashokkumar_LoginAsCustomer::login_action
Ashokkumar_LoginAsCustomer::audit_log
Ashokkumar_LoginAsCustomer::config
```

### 5. Route Check
```bash
bin/magento info:adminhtml:routes | grep loginascustomer
```
**Expected:** `loginascustomer` route registered

### 6. Menu Check
Login to Admin Panel, navigate to:
- ✅ **Customers** menu
- ✅ **Login as Customer** submenu visible

### 7. Grid Action Check
- ✅ **Customers > All Customers**
- ✅ Actions dropdown has **Login as Customer**

### 8. Customer Edit Button Check
- ✅ **Customers > Edit Customer**
- ✅ **Login as Customer** button in header

### 9. Configuration Page Check
- ✅ **Stores > Configuration > Ashokkumar**
- ✅ **Login as Customer** section visible
- ✅ All 4 settings present

### 10. Audit Log Grid Check
- ✅ **Customers > Login as Customer** menu works
- ✅ Grid displays with columns

---

## Functional Test

### Test 1: Generate Token
1. Navigate to **Customers > All Customers**
2. Click **Actions > Login as Customer** on any customer
3. **Expected:** New window opens with customer logged in

### Test 2: Verify Audit Log
1. Navigate to **Customers > Login as Customer**
2. **Expected:** New row with:
   - Your admin username
   - Customer email
   - Status: **Success**
   - Your IP address
   - Timestamp

### Test 3: Token Expiry
1. Generate token URL (don't click immediately)
2. Wait 6 minutes
3. Click the old token URL
4. **Expected:** Error message "Login link has expired"
5. Check audit log: Status = **Expired**

### Test 4: Token Reuse Prevention
1. Generate token
2. Click token (login succeeds)
3. Copy the URL, paste in new tab
4. **Expected:** Error message, token already used
5. Check audit log: Only 1 success entry

### Test 5: Permission Enforcement
1. Create new admin role WITHOUT Login as Customer permission
2. Assign test admin user to that role
3. Login as test admin
4. **Expected:** No "Login as Customer" button visible

---

## Security Validation

### 1. Token Hash Check
```bash
# After generating a token, check database
mysql -u your_user -p your_database -e "
SELECT token_hash, LENGTH(token_hash) as hash_length 
FROM ashokkumar_login_as_customer_log 
ORDER BY created_at DESC LIMIT 1;"
```
**Expected:** hash_length = 64 (SHA-256)

### 2. No Plaintext Token
```bash
# Search for any plaintext token storage
grep -r "token" /var/www/html/ashokkumar.io/project/var/log/ | grep -v "hash"
```
**Expected:** No plaintext tokens found

### 3. ACL Enforcement
```bash
# Try accessing audit log URL without permission
curl -I http://your-domain.com/admin/loginascustomer/audit/index
```
**Expected:** 403 Forbidden or redirect to login

### 4. CSRF Protection
```bash
# Try POST without form_key
curl -X POST http://your-domain.com/admin/loginascustomer/login/generate
```
**Expected:** 403 or validation error

---

## Performance Validation

### 1. Index Check
```bash
mysql -u your_user -p your_database -e "
SHOW INDEX FROM ashokkumar_login_as_customer_log;"
```
**Expected:** 5 indexes (admin_id, customer_id, token_hash, status, created_at)

### 2. Query Performance
```bash
# Enable slow query log temporarily
mysql -u your_user -p your_database -e "
EXPLAIN SELECT * FROM ashokkumar_login_as_customer_log 
WHERE token_hash = 'abc123' AND status = 'pending' LIMIT 1;"
```
**Expected:** Uses index on token_hash

---

## Rollback (If Needed)

```bash
# Disable module
bin/magento module:disable Ashokkumar_LoginAsCustomer

# Run setup
bin/magento setup:upgrade

# Clear cache
bin/magento cache:flush

# Optional: Drop table
mysql -u your_user -p your_database -e "
DROP TABLE IF EXISTS ashokkumar_login_as_customer_log;"
```

---

## Common Issues & Solutions

### Issue: Module not showing in module:status
**Solution:**
```bash
bin/magento setup:upgrade --keep-generated
bin/magento cache:flush
```

### Issue: Database table not created
**Solution:**
```bash
# Force schema upgrade
bin/magento setup:db-schema:upgrade
```

### Issue: DI compilation error
**Solution:**
```bash
rm -rf generated/*
bin/magento setup:di:compile
```

### Issue: Button not visible
**Solution:**
1. Clear cache: `bin/magento cache:flush`
2. Check permissions: System > User Roles
3. Reindex if needed: `bin/magento indexer:reindex`

### Issue: Token validation fails
**Solution:**
1. Check system time synchronization
2. Verify token_lifetime config
3. Check audit_log table for entry

---

## Success Criteria

✅ Module status shows "enabled"  
✅ Database table created with 12 columns  
✅ ACL resources registered (4 resources)  
✅ Admin menu item visible  
✅ Customer grid action visible  
✅ Customer edit button visible  
✅ Configuration page accessible  
✅ Audit log grid functional  
✅ Token generation works  
✅ Customer login successful  
✅ Audit log entries created  
✅ Token expiry enforced  
✅ Token reuse prevented  
✅ Permissions enforced  
✅ Security validations pass  

---

## Production Deployment Checklist

Before deploying to production:

- [ ] Test on staging environment
- [ ] Verify HTTPS enforcement
- [ ] Configure token lifetime appropriately
- [ ] Set up admin 2FA (if not already)
- [ ] Review admin role permissions
- [ ] Test with various customer types
- [ ] Verify audit log retention policy
- [ ] Set up monitoring/alerts for failed attempts
- [ ] Document internal procedures
- [ ] Train admin users on feature
- [ ] Schedule regular audit log reviews
- [ ] Backup database before installation
- [ ] Plan rollback procedure
- [ ] Test during maintenance window
- [ ] Monitor logs post-deployment

---

## Support

If verification fails:
1. Check error logs: `var/log/system.log`, `var/log/exception.log`
2. Review module files for syntax errors
3. Verify Magento version compatibility (2.4.4+)
4. Check PHP version (8.1+)
5. Ensure database permissions correct

**Contact:** Ashokkumar Development Team  
**Documentation:** README.md, IMPLEMENTATION_SUMMARY.md
