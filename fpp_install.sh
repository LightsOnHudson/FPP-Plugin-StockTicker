#!/bin/bash
pushd $(dirname $(which $0))
exec /opt/fpp/scripts/update_plugin StockTicker
popd
