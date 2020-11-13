local WikiSEO = {}
local php

function WikiSEO.set( args )
    return php.set( args )
end


function WikiSEO.setupInterface()
    -- Boilerplate
    WikiSEO.setupInterface = nil
    php = mw_interface
    mw_interface = nil

    -- Register this library in the "mw" global
    mw = mw or {}
    mw.ext = mw.ext or {}
    mw.ext.seo = WikiSEO

    package.loaded['mw.ext.seo'] = WikiSEO
end

return WikiSEO