<template>
    <div>
        <Heading
            variant="small"
            title="Manage Passkeys"
            description="Add and manage your passkeys"
        />
        <!-- محتوای کامپوننت -->
    </div>
</template>

<script setup lang="ts">
import Heading from '@/components/Heading.vue';

// ✅ این Props رو حتماً export کن
export interface Props {
    canManagePasskeys?: boolean;
    passkeys?: any[];
}

defineProps<Props>();
</script>