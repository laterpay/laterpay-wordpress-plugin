module.exports = memoize;

function memoize(fn, options){
  var mem = {}, queues = {};
  var hash = typeof options == 'function' ? options : options && options.hash;
  var methods = {
    read: options && options.read || read,
    write: options && options.write || write
  };

  !hash && (hash = defaultHashFn);

  return call;

  function wait (key, callback) {
    if (key in queues) {
      queues[key].push(callback);
      return true;
    }

    queues[key] = [callback];
  }

  function read (key, callback) {
    if (key in mem) {
      callback(undefined, mem[key]);
      return;
    }

    callback(true);
  }

  function write (key, value, callback) {
    mem[key] = value;
    callback();
  }

  function call () {
    var args = Array.prototype.slice.call(arguments);
    var key = hash.apply(undefined, args);
    var callback = args.pop();

    methods.read(key, function (notexists, value) {
      if (!notexists) {
        return callback.apply(undefined, value);
      }

      if (wait(key, callback)) return;

      args.push(done);
      fn.apply(undefined, args);
    });

    function done (error){
      var params = arguments;

      if (error) {
        run(queues[key], params);
        delete queues[key];
        return;
      }

      methods.write(key, params, function (error) {
        if (error) {
          process.nextTick(function(){
            throw error;
          });
        }

        run(queues[key], params);
        delete queues[key];
      });
    }
  };
};

function defaultHashFn (n) {
  if (typeof n != 'function') return n;
  return 'nil';
}


function run (callbacks, params){
  var i = callbacks.length;

  while (i--){
    try {
      callbacks[i].apply(undefined, params);
    } catch(err){
      process.nextTick(function(){
        throw err;
      });
    }
  }

}
