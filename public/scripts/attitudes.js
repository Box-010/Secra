(() => {
  function newAttitude(attitudeableType, attitudeableId, attitudeType) {
    const formData = new FormData();
    formData.append('attitude_type', attitudeType);
    fetch(`./attitudes/${attitudeableType}/${attitudeableId}`,
      {
        method: 'POST',
        body: formData
      })
      .then(res => {
        if (res.status === 200) {
          return res.json();
        } else {
          throw new Error(`Failed to attitude: ${res.status} ${res.statusText}`);
        }
      })
      .then(res => {
        if (res) {
          const {success, data: {positive_count, negative_count}} = res;
          if (success) {
            const positiveButton = document.querySelector(`.attitude-button[data-attitudeable-type="${attitudeableType}"][data-attitudeable-id="${attitudeableId}"][data-attitude-type="positive"]`);
            const negativeButton = document.querySelector(`.attitude-button[data-attitudeable-type="${attitudeableType}"][data-attitudeable-id="${attitudeableId}"][data-attitude-type="negative"]`);
            if (positiveButton) {
              positiveButton.dataset.count = positive_count;
              positiveButton.dataset.attituded = attitudeType === 'positive' ? '1' : '0';
              positiveButton.querySelector('.attitude-button-count').textContent = positive_count > 0 ? positive_count : '';
            }
            if (negativeButton) {
              negativeButton.dataset.count = negative_count;
              negativeButton.dataset.attituded = attitudeType === 'negative' ? '1' : '0';
              negativeButton.querySelector('.attitude-button-count').textContent = negative_count > 0 ? negative_count : '';
            }
          }
        }
      })
      .catch(error => {
        console.error(error)
      })
  }

  function initAttitudes() {
    const attitudeButtons = document.querySelectorAll('.attitude-button');

    attitudeButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        console.log(button.dataset);
        const {attitudeableType, attitudeableId, attitudeType, attituded} = button.dataset;
        if (!attitudeableType || !attitudeableId || !attitudeType || !attituded) {
          return;
        }
        if (attituded === '1') {
          newAttitude(attitudeableType, attitudeableId, 'neutral');
        } else {
          newAttitude(attitudeableType, attitudeableId, attitudeType);
        }
      });
    });
  }

  window.initAttitudes = initAttitudes;

  document.addEventListener('DOMContentLoaded', initAttitudes);
})();