import { router } from '@inertiajs/react';
import { useEffect } from 'react';

const DEFAULT_EVENTS = [
    'QueueCreated',
    'QueueCalled',
    'QueueUpdated',
    'QueueCompleted',
    'PracticeSessionUpdated',
];

export default function useRealtimeReload({
    publicChannels = [],
    privateChannels = [],
    only = [],
    events = DEFAULT_EVENTS,
}) {
    useEffect(() => {
        if (!window.Echo) {
            return undefined;
        }

        const reload = () => {
            router.reload({
                only,
                preserveScroll: true,
                preserveState: true,
            });
        };

        const channelNames = [
            ...publicChannels.filter(Boolean),
            ...privateChannels.filter(Boolean),
        ];

        publicChannels.filter(Boolean).forEach((name) => {
            const channel = window.Echo.channel(name);

            events.forEach((eventName) => {
                channel.listen(`.${eventName}`, reload);
            });
        });

        privateChannels.filter(Boolean).forEach((name) => {
            const channel = window.Echo.private(name);

            events.forEach((eventName) => {
                channel.listen(`.${eventName}`, reload);
            });
        });

        return () => {
            channelNames.forEach((name) => {
                window.Echo.leave(name);
            });
        };
    }, [events, only, privateChannels, publicChannels]);
}
