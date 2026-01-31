# Add project specific ProGuard rules here.
# By default, the flags in this file are appended to flags specified
# in /usr/local/Cellar/android-sdk/24.3.3/tools/proguard/proguard-android.txt
# You can edit the include path and order by changing the proguardFiles
# directive in build.gradle.
#
# For more details, see
#   http://developer.android.com/guide/developing/tools/proguard.html

# Add any project specific keep options here:

# Dejavoo SoftPOS SDK
-keep class * implements java.io.Serializable
-keep class com.denovo.app.invokeiposgo.* { *; }
-keep class com.denovo.app.invokeiposgo.models.** { *; }
-keep class com.google.gson.** { *; }
-keep class com.google.gson.stream.** { *; }
-keep class com.cardtek.softpos.** { *; }
-keep class com.denovo.app.top.** { *; }
-keep class orion.acquila.libra.** { *; }
-keep class Lorion.acquila.libra.ji.** { *; }

-keepattributes Signature
-keepattributes Exceptions, Signature, InnerClasses
-keep class sun.misc.* { *; }
