import Tenant from './Tenant'
import Central from './Central'
import Settings from './Settings'
const Controllers = {
    Tenant: Object.assign(Tenant, Tenant),
Central: Object.assign(Central, Central),
Settings: Object.assign(Settings, Settings),
}

export default Controllers