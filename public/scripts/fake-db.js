(() => {
  function FakeDB(dbName, callback) {
    this.db = null;
    this.dbName = dbName || 'fakeDB';
    this.init().then(() => {
      if (callback) {
        callback(this);
      }
    });
  }

  FakeDB.prototype.init = function () {
    if (window.indexedDB) {
      return this.__initIndexedDB();
    } else {
      return this.__initLocalStorage();
    }
  };

  FakeDB.prototype.__initIndexedDB = function () {
    var self = this;
    return new Promise(function (resolve, reject) {
      var request = window.indexedDB.open(self.dbName);
      request.onerror = function (event) {
        console.error('indexedDB error:', event);
        reject(event);
      };
      request.onsuccess = function (event) {
        self.db = event.target.result;
        resolve();
      };
      request.onupgradeneeded = function (event) {
        self.db = event.target.result;
        if (!self.db.objectStoreNames.contains(self.dbName)) {
          self.db.createObjectStore(self.dbName, { keyPath: 'key' });
        }
      };
    });
  };

  FakeDB.prototype.__initLocalStorage = function () {
    if (!window.localStorage) {
      console.error('localStorage is not supported');
      return Promise.reject('localStorage is not supported');
    }
    return Promise.resolve();
  };

  FakeDB.prototype.set = function (key, value) {
    if (this.db) {
      return this.__setIndexedDB(key, value);
    } else {
      return this.__setLocalStorage(key, value);
    }
  }

  function checkValueIsNullOrUndefined(value) {
    return typeof value === 'undefined' || value === null;
  }

  function checkValueSaveable(value) {
    return !(typeof value === 'function' || typeof value === 'symbol' || checkValueIsNullOrUndefined(value));
  }

  FakeDB.prototype.__setIndexedDB = function (key, value) {
    var self = this;
    return new Promise(function (resolve, reject) {
      if (!checkValueSaveable(value)) {
        reject('value is not saveable');
      }
      var transaction = self.db.transaction(self.dbName, 'readwrite');
      var store = transaction.objectStore(self.dbName);
      var request = store.put({ key: key, value: value });
      request.onsuccess = function (event) {
        resolve();
      };
      request.onerror = function (event) {
        reject(event.target.error);
      };
    });
  };

  FakeDB.prototype.__setLocalStorage = function (key, value) {
    const realKey = `${this.dbName}-${key}`;
    if (!checkValueSaveable(value)) {
      return Promise.reject('value is not saveable');
    }
    window.localStorage.setItem(realKey, JSON.stringify(value));
    window.localStorage.setItem(`${realKey}-type`, typeof value);
    return Promise.resolve();
  }

  FakeDB.prototype.get = function (key, defaultValue) {
    if (this.db) {
      return this.__getIndexedDB(key, defaultValue);
    } else {
      return this.__getLocalStorage(key, defaultValue);
    }
  };

  FakeDB.prototype.__getIndexedDB = function (key, defaultValue) {
    var self = this;
    return new Promise(function (resolve, reject) {
      var transaction = self.db.transaction(self.dbName, 'readonly');
      var store = transaction.objectStore(self.dbName);
      var request = store.get(key);
      request.onsuccess = function (event) {
        if (event.target.result) {
          resolve(event.target.result.value);
        } else {
          resolve(defaultValue);
        }
      };
      request.onerror = function (event) {
        reject(event.target.error);
      };
    });
  };

  FakeDB.prototype.__getLocalStorage = function (key, defaultValue) {
    const realKey = `${this.dbName}-${key}`;
    return new Promise(function (resolve, _) {
      const value = window.localStorage.getItem(realKey);
      const type = window.localStorage.getItem(`${realKey}-type`);
      if (value && type) {
        if (type === 'number') {
          resolve(Number(value));
        } else if (type === 'boolean') {
          resolve(Boolean(value));
        } else if (type === 'bigint') {
          resolve(BigInt(value));
        } else {
          resolve(JSON.parse(value));
        }
      } else {
        resolve(defaultValue);
      }
    });
  };

  FakeDB.prototype.delete = function (key) {
    if (this.db) {
      this.__deleteIndexedDB(key);
    } else {
      this.__deleteLocalStorage(key);
    }
  };

  FakeDB.prototype.__deleteIndexedDB = function (key) {
    var self = this;
    return new Promise(function (resolve, reject) {
      var transaction = self.db.transaction(self.dbName, 'readwrite');
      var store = transaction.objectStore(self.dbName);
      var request = store.delete(key);
      request.onsuccess = function (event) {
        resolve(event);
      };
      request.onerror = function (event) {
        reject(event);
      };
    });
  };

  FakeDB.prototype.__deleteLocalStorage = function (key) {
    const realKey = `${this.dbName}-${key}`;
    window.localStorage.removeItem(realKey);
  };

  FakeDB.prototype.clear = function () {
    if (this.db) {
      this.__clearIndexedDB();
    } else {
      this.__clearLocalStorage();
    }
  };

  FakeDB.prototype.__clearIndexedDB = function () {
    var self = this;
    return new Promise(function (resolve, reject) {
      var transaction = self.db.transaction(self.dbName, 'readwrite');
      var store = transaction.objectStore(self.dbName);
      var request = store.clear();
      request.onsuccess = function (event) {
        resolve(event);
      };
      request.onerror = function (event) {
        reject(event);
      };
    });
  };

  FakeDB.prototype.__clearLocalStorage = function () {
    for (var i = 0; i < window.localStorage.length; i++) {
      var key = window.localStorage.key(i);
      if (key.startsWith(this.dbName)) {
        window.localStorage.removeItem(key);
      }
    }
  };

  window.FakeDB = FakeDB;
})();