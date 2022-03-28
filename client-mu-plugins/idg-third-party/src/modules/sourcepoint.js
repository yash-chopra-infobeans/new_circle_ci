/* eslint-disable no-underscore-dangle */
import loadScript from './loadScript';
import onDocumentReady from './onDocumentReady';

const TCV_VERSION = 2;
const { IDG } = window;

/**
 * Add click events to cmp buttons.
 */
const clickEvents = () => {
  const eeaButton = document.querySelector('#eea-consent-ui button');
  const ccpaButton = document.querySelector('#ccpa-consent-ui button');

  if (eeaButton) {
    eeaButton.addEventListener('click', e => {
      e.preventDefault();
      window._sp_.loadPrivacyManagerModal(Number(IDG?.settings.cmp.gdpr.privacy_manager_id));
    });
  }

  if (ccpaButton) {
    ccpaButton.addEventListener('click', e => {
      e.preventDefault();
      window._sp_ccpa.loadPrivacyManagerModal(
        Number(IDG?.settings.cmp.ccpa.privacy_manager_id),
        IDG?.settings?.cmp?.ccpa?.privacy_manager_uuid || '',
      );
    });
  }
};

/**
 * Toggle privacy manager button.
 *
 * @param {object} state - The consent state.
 */
const togglePrivacyManagerButton = state => {
  onDocumentReady(() => {
    const eeaButton = document.querySelector('#eea-consent-ui');

    if (!eeaButton) {
      return;
    }

    if (!state?.gdprApplies) {
      eeaButton.style.display = 'none';
      return;
    }

    eeaButton.style.display = 'block';
  });
};

/**
 * Toggle do not sell my data button
 *
 * @param {object} state - The consent state.
 */
const toggleDoNotSellMyDataButton = state => {
  onDocumentReady(() => {
    const ccpaButton = document.querySelector('#ccpa-consent-ui');

    if (!ccpaButton) {
      return;
    }

    if (!state?.ccpaApplies) {
      ccpaButton.style.display = 'none';
      return;
    }

    ccpaButton.style.display = 'block';
  });
};

const CMP = () => {
  // eslint-disable-next-line no-underscore-dangle
  window._sp_ = {
    config: {
      accountId: Number(IDG?.settings.cmp.account.id),
      baseEndpoint: IDG?.settings.cmp.account.base_endpoint,
      propertyHref: IDG?.settings.cmp.account.href,
      targetingParams: {
        type: 'GDPR',
      },
    },
  };

  // eslint-disable-next-line no-underscore-dangle
  window._sp_ccpa = {
    config: {
      accountId: Number(IDG?.settings.cmp.account.id),
      mmsDomain: IDG?.settings.cmp.account.base_endpoint,
      ccpaOrigin: IDG?.settings.cmp.ccpa.origin,
      siteHref: IDG?.settings.cmp.account.href,
      getDnsMsgMms: true,
      alwaysDisplayDns: false,
      targetingParams: {
        type: 'CCPA',
      },
    },
  };

  Promise.all([IDG?.settings.cmp.gdpr.script, IDG?.settings.cmp.ccpa.script].map(loadScript));

  onDocumentReady(clickEvents);

  const events = [togglePrivacyManagerButton, toggleDoNotSellMyDataButton];
  let state = {};

  // eslint-disable-next-line no-underscore-dangle
  window.__tcfapi('addEventListener', TCV_VERSION, (tcData, success) => {
    if (!success) {
      return;
    }

    if (!['tcloaded', 'useractioncomplete'].includes(tcData.eventStatus)) {
      return;
    }

    if (tcData?.gdprApplies) {
      // eslint-disable-next-line no-underscore-dangle
      window.__tcfapi('getCustomVendorConsents', TCV_VERSION, consent => {
        state = {
          gdprApplies: true,
          ccpaApplies: false,
          event: tcData.eventStatus,
          ...consent,
        };

        events.forEach(callback => callback(state));
      });

      return;
    }

    // eslint-disable-next-line no-underscore-dangle
    window.__uspapi('getCustomVendorRejects', TCV_VERSION, (consent, USPDataSuccess) => {
      if (consent?.ccpaApplies) {
        state = {
          gdprApplies: false,
          ccpaApplies: true,
          event: tcData.eventStatus,
          ...consent,
        };

        events.forEach(callback => callback(state));

        return;
      }

      state = {
        gdprApplies: false,
        ccpaApplies: false,
        event: tcData.eventStatus,
      };

      if (USPDataSuccess) {
        events.forEach(callback => callback(state));
      }
    });
  });

  return {
    onConsentApplied: callback => events.push(callback),
    hasConsentForVendor: vendorId => {
      if (!state?.gdprApplies && !state?.ccpaApplies) {
        return true;
      }

      if (!vendorId) {
        return false;
      }

      if (state?.gdprApplies) {
        return (state?.consentedVendors || []).some(({ _id }) => _id === vendorId);
      }

      return !(state?.rejectedVendors || []).some(({ _id }) => _id === vendorId);
    },
    hasConsentForPurpose: () => {
      // @TODO not sure if needed.
    },
  };
};

export default CMP;
