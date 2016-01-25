<?php
/**
 * Created by IntelliJ IDEA.
 * User: hanikel
 * Date: 22.12.15
 * Time: 16:53
 */

namespace Php\Time\Zone;


use Php\Time\ZoneOffset;

class DummyZoneRulesProvider extends ZoneRulesProvider
{

    /**
     * SPI method to get the available zone IDs.
     * <p>
     * This obtains the IDs that this {@code ZoneRulesProvider} provides.
     * A provider should provide data for at least one zone ID.
     * <p>
     * The returned zone IDs remain available and valid for the lifetime of the application.
     * A dynamic provider may increase the set of IDs as more data becomes available.
     *
     * @return string[] the set of zone IDs being provided, not null
     * @throws ZoneRulesException if a problem occurs while providing the IDs
     */
    protected function provideZoneIds()
    {
        return ['Europe/Paris', 'Europe/Berlin', 'UTC'];
    }

    /**
     * SPI method to get the rules for the zone ID.
     * <p>
     * This loads the rules for the specified zone ID.
     * The provider implementation must validate that the zone ID is valid and
     * available, throwing a {@code ZoneRulesException} if it is not.
     * The result of the method in the valid case depends on the caching flag.
     * <p>
     * If the provider implementation is not dynamic, then the result of the
     * method must be the non-null set of rules selected by the ID.
     * <p>
     * If the provider implementation is dynamic, then the flag gives the option
     * of preventing the returned rules from being cached in {@link ZoneId}.
     * When the flag is true, the provider is permitted to return null, where
     * null will prevent the rules from being cached in {@code ZoneId}.
     * When the flag is false, the provider must return non-null rules.
     *
     * @param string $zoneId the zone ID as defined by {@code ZoneId}, not null
     * @param bool $forCaching whether the rules are being queried for caching,
     * true if the returned rules will be cached by {@code ZoneId},
     * false if they will be returned to the user without being cached in {@code ZoneId}
     * @return ZoneRules the rules, null if {@code forCaching} is true and this
     * is a dynamic provider that wants to prevent caching in {@code ZoneId},
     * otherwise not null
     * @throws ZoneRulesException if rules cannot be obtained for the zone ID
     */
    protected function provideRules($zoneId, $forCaching)
    {
        return ZoneRules::ofOffset(ZoneOffset::ofHours(1));
    }

    /**
     * SPI method to get the history of rules for the zone ID.
     * <p>
     * This returns a map of historical rules keyed by a version string.
     * The exact meaning and format of the version is provider specific.
     * The version must follow lexicographical order, thus the returned map will
     * be order from the oldest known rules to the newest available rules.
     * The default 'TZDB' group uses version numbering consisting of the year
     * followed by a letter, such as '2009e' or '2012f'.
     * <p>
     * Implementations must provide a result for each valid zone ID, however
     * they do not have to provide a history of rules.
     * Thus the map will contain at least one element, and will only contain
     * more than one element if historical rule information is available.
     * <p>
     * The returned versions remain available and valid for the lifetime of the application.
     * A dynamic provider may increase the set of versions as more data becomes available.
     *
     * @param string $zoneId the zone ID as defined by {@code ZoneId}, not null
     * @return ZoneRules[] a modifiable copy of the history of the rules for the ID, sorted
     *  from oldest to newest, not null
     * @throws ZoneRulesException if history cannot be obtained for the zone ID
     */
    protected function provideVersions($zoneId)
    {
        // TODO: Implement provideVersions() method.
    }
}