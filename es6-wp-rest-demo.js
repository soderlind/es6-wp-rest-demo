/**
 * ES6 WP REST Demo
 *
 * Uses REST Route in es6-wp-rest-demo.php
 *
 * @author Per Soderlind http://soderlind.no
 *
 */
document.addEventListener("DOMContentLoaded", () => {
  // Wait util the webpage is loaded
  let button = document.getElementById("es6-demo-input"); // The form button
  let output = document.getElementById("es6-demo-output"); // The output area

  button.onclick = async (event) => {
    // Fire the event when the button is clicked.
    event.preventDefault();

    const self = event.currentTarget; // "this" button
    const data = JSON.stringify({
      // The data to send, in this case a JSON string: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/JSON/stringify
      sum: self.dataset.sum,
    });

    const url = pluginES6WPREST.restURL; // set the rest url, added at https://github.com/soderlind/es6-wp-rest-demo/blob/master/es6-wp-rest-demo.php#L112
    try {
      const response = await fetch(url, {
        headers: new Headers({
          "X-WP-Nonce": pluginES6WPREST.nonce, // set the nonce, added at https://github.com/soderlind/es6-wp-rest-demo/blob/master/es6-wp-rest-demo.php#L111
          "content-type": "application/json",
        }),
        method: "POST",
        credentials: "same-origin",
        body: data,
      });

      const res = await response.json(); // read the json response from https://github.com/soderlind/es6-wp-rest-demo/blob/master/es6-wp-rest-demo.php#L69
      if (res.response === "success") {
        self.dataset.sum = res.data; // Update the data-sum attribute with the incremented value.
        output.innerHTML = res.data; // Display the updated value.
        console.log(res);
      } else {
        console.error(res);
      }
    } catch (err) {
      console.error(err);
    }
  };
});
