const ErrorLogger = function(Core) {
   //
   this.Core = Core;
};

ErrorLogger.prototype.send = async function(msg) {
   this.Core.store(
      'log',
      {msg: msg},
      {
         response: () => {
            console.log('response');
            this.Core.getResponseObj();
         },
         reject: () => {
            console.log('reject');
            this.Core.getResponseObj();
         },
      },
   );
};
