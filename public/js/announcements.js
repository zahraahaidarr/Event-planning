// If you later add image upload or live preview, hook it here.
// For now, we keep it minimal on purpose since your DB has no image column.

// Optional: block employees from changing hidden audience via devtools
(function(){
  const form = document.querySelector('form');
  if(!form) return;

  const hiddenAud = form.querySelector('input[name="audience"][type="hidden"]');
  if(hiddenAud){
    // lock value to workers in case someone tries to tamper with it in devtools
    hiddenAud.value = 'workers';
  }
})();
