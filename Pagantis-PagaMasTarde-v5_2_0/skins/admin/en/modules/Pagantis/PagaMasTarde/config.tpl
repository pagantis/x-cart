{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * PagaMasTarde configuration page
 *
 * @author    Qualiteam software Ltd <info@x-cart.com>
 * @copyright Copyright (c) 2011-2016 Qualiteam software Ltd <info@x-cart.com>. All rights reserved
 * @license   http://www.x-cart.com/license-agreement.html X-Cart 5 License Agreement
 * @link      http://www.x-cart.com/
 *}

<table cellspacing="1" cellpadding="5" class="settings-table">

  <tr>
    <td colspan="2" class="note">
To obtain the transaction key from the Merchant Interface, do the following:
<ol>
  <li>Log into the Merchant Interface</li>
  <li>Select Settings from the Main Menu</li>
  <li>Click on Obtain Transaction Key in the Security section</li>
  <li>Type in the answer to the secret question configured on setup</li>
  <li>Click Submit</li>
</ol>
    </td>
  </tr>

  <tr>
    <td class="setting-name">
    <label for="settings_testPublicKey">{t(#Test Public Key#)}</label>
    </td>
    <td>
    <input type="text" id="settings_testPublicKey" name="settings[testPublicKey]" value="{paymentMethod.getSetting(#testPublicKey#)}" class="validate[required,maxSize[255]]" />
    </td>
  </tr>

  <tr>
    <td class="setting-name">
    <label for="settings_testSecretKey">{t(#Test Secret Key#)}</label>
    </td>
    <td>
    <input type="text" id="settings_testSecretKey" name="settings[testSecretKey]" value="{paymentMethod.getSetting(#testSecretKey#)}" class="validate[required,maxSize[255]]" />
    </td>
  </tr>

  <tr>
    <td class="setting-name">
    <label for="settings_realPublicKey">{t(#Real Public Key#)}</label>
    </td>
    <td>
    <input type="text" id="settings_realPublicKey" name="settings[realPublicKey]" value="{paymentMethod.getSetting(#realPublicKey#)}" class="validate[required,maxSize[255]]" />
    </td>
  </tr>

  <tr>
    <td class="setting-name">
    <label for="settings_realSecretKey">{t(#Real Secret Key#)}</label>
    </td>
    <td>
    <input type="text" id="settings_realSecretKey" name="settings[realSecretKey]" value="{paymentMethod.getSetting(#realSecretKey#)}" class="validate[required,maxSize[255]]" />
    </td>
  </tr>


  <tr>
    <td class="setting-name">
    <label for="settings_discount">{t(#Discount#)}</label>
    </td>
    <td>
    <select id="settings_discount" name="settings[discount]">
      <option value="1" selected="{isSelected(paymentMethod.getSetting(#discount#),#1#)}">true</option>
      <option value="0" selected="{isSelected(paymentMethod.getSetting(#discount#),#0#)}">false</option>
    </select>
    </td>
  </tr>

  <tr>
    <td class="setting-name">
    <label for="settings_test">{t(#Test/Live mode#)}</label>
    </td>
    <td>
    <select id="settings_test" name="settings[test]">
      <option value="1" selected="{isSelected(paymentMethod.getSetting(#test#),#1#)}">{t(#Test mode: Test#)}</option>
      <option value="0" selected="{isSelected(paymentMethod.getSetting(#test#),#0#)}">{t(#Test mode: Live#)}</option>
    </select>
    </td>
  </tr>

</table>
