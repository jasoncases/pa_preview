const DataSetter = (() => {
  const capturedSetters = document.querySelectorAll('[id^="set:"]');
  if (!capturedSetters) return;
  capturedSetters.forEach((setter) => {
    const targetId = setter.id.split(':')[1];
    const node = <HTMLInputElement>document.getElementById(targetId);
    if (node.type === 'checkbox' || node.type === 'radio') {
      let val: any = (<HTMLInputElement>setter).value;
      if (val.match(/[0-9]+/)) val = parseInt(val);
      node.checked = Boolean(val);
    } else {
      node.value = (<HTMLInputElement>setter).value;
    }
  });
})();

